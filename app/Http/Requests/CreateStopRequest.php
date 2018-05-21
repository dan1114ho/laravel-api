<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\TourStop;
use Illuminate\Validation\Rule;

class CreateStopRequest extends FormRequest
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
            'title' => 'required|string|max:255|min:3',
            'description' => 'required|string|max:2000|min:3',
            // 'location_type' => [
            //     'required',
            //     Rule::in(TourStop::$LOCATION_TYPES),
            // ],
        ];
    }
}