<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Tour;
use App\Rules\YoutubeVideo;

class UpdateTourRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                'min:3',
                Rule::unique('tours', 'title')->ignore(request()->route('tour')),
            ],
            'description' => 'required|string|max:16000|min:3',
            'pricing_type' => [
                'required',
                Rule::in(Tour::$PRICING_TYPES),
            ],
            'type' => [
                'required',
                Rule::in(Tour::$TOUR_TYPES),
            ],

            'location.address1' => 'nullable|string|max:255',
            'location.address2' => 'nullable|string|max:255',
            'location.city' => 'nullable|string|max:100',
            'location.state' => 'nullable|string|max:2',
            'location.zipcode' => 'nullable|string|max:12',
            'location.latitude' => ['nullable', 'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'],
            'location.longitude' => ['nullable', 'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'],

            'facebook_url' => 'nullable|string|regex:/(facebook.com\/[a-zA-Z0-9])/u',
            'twitter_url' => 'nullable|string|regex:/(twitter.com\/[a-zA-Z0-9])/u',
            'instagram_url' => 'nullable|string|regex:/(instagram.com\/[a-zA-Z0-9])/u',

            'video_url' => ['nullable', 'url', new YoutubeVideo],
            'start_video_url' => ['nullable', 'url', new YoutubeVideo],
            'end_video_url' => ['nullable', 'url', new YoutubeVideo],

            'start_message' => 'nullable|string|max:1000',
            'end_message' => 'nullable|string|max:1000',

            'has_prize' => 'nullable|boolean',
            'prize_details' => 'nullable|string|max:1000',
            'prize_instructions' => 'nullable|string|max:1000',

            'start_point_id' => [
                'nullable',
                'numeric',
                Rule::exists('tour_stops', 'id')->where(function ($query) {
                    $query->where('tour_id', $this->route('tour')->id);
                }),
            ],
            'end_point_id' => [
                'nullable',
                'numeric',
                Rule::exists('tour_stops', 'id')->where(function ($query) {
                    $query->where('tour_id', $this->route('tour')->id);
                }),
            ],

            'main_image_id' => 'nullable|integer|exists:media,id',
            'start_image_id' => 'nullable|integer|exists:media,id',
            'end_image_id' => 'nullable|integer|exists:media,id',
            'pin_image_id' => 'nullable|integer|exists:media,id',
            'image1_id' => 'nullable|integer|exists:media,id',
            'image2_id' => 'nullable|integer|exists:media,id',
            'image3_id' => 'nullable|integer|exists:media,id',
            'trophy_image_id' => 'nullable|integer|exists:media,id',
            'intro_audio_id' => 'nullable|integer|exists:media,id',
            'background_audio_id' => 'nullable|integer|exists:media,id',

            'route' => 'nullable',
        ];
    }
}
