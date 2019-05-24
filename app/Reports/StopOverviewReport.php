<?php

namespace App\Reports;

use App\StopStat;
use App\Activity;

class StopOverviewReport extends BaseReport
{
    /**
     * The begin date.
     *
     * @var string
     */
    protected $start_date;

    /**
     * The end date.
     *
     * @var string
     */
    protected $end_date;

    /**
     * Filter the results to between two dates.
     *
     * @param string $start
     * @param string $end
     * @return $this
     */
    public function forDates($start, $end)
    {
        $this->start_date = $start;
        $this->end_date = $end;

        return $this;
    }

    /**
     * Run the report and return the results.
     *
     * @return array
     */
    public function run()
    {
        $results = [];

        $stops = $this->tour->stops->pluck('id');

        $stats = StopStat::whereIn('stop_id', $stops)
            ->betweenDates($this->start_date, $this->end_date)
            ->groupBy('stop_id')
            ->selectRaw('sum(time_spent) as time_spent, sum(actions) as actions, stop_id')
            ->get();

        foreach ($this->tour->stops as $stop) {
            $stat = $stats->where('stop_id', $stop->id)->first();
            $activities = Activity::where('actionable_id', $stop->id)
                ->where('actionable_type', 'App\TourStop')
                ->orderBy('device_id')
                ->orderBy('created_at')
                ->get();

            $visits = 0;
            $timeSeconds = 0;
            for ($i = 0; $i < count($activities); $i ++) {
                if ($i == count($activities) - 1) {
                    if ($activities[$i]->action == 'start') {
                        $timeSeconds += 600;
                    }
                    continue;
                }
                if ($activities[$i]->action == 'start' && $activities[$i + 1]->action == 'start') {
                    $timeSeconds += 600;
                }
                else if ($activities[$i]->action == 'stop' && $activities[$i + 1]->action == 'stop') {
                    $timeSeconds += 600;
                }
                else if ($activities[$i]->action == 'start' && $activities[$i + 1]->action == 'stop') {
                    $end = strtotime($activities[$i + 1]->created_at);
                    $start = strtotime($activities[$i]->created_at);
                    $timeSeconds += $end - $start;
                }

                if ($activities[$i]->action == 'start') {
                    $visits ++;
                }
            }
            $minutes = round($timeSeconds / 60);

            array_push($results, [
                'id' => $stop->id,
                'order' => $stop->order,
                'title' => $stop->title,
                'time' => $minutes,
                'visits' => count($activities),
                'actions' => (int) $stat['actions']
            ]);
        }

        return ['stops' => $results];
    }
}
