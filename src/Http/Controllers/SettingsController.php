<?php

namespace Epigra\NovaSettings\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Epigra\NovaSettings\NovaSettingsTool;
use Laravel\Nova\Contracts\Resolvable;
use Laravel\Nova\Http\Requests\NovaRequest; 

class SettingsController extends Controller
{
    public function get(Request $request)
    {
        $fields = collect(NovaSettingsTool::getSettingsFields());
        
        $fields->whereInstanceOf(Resolvable::class)->each(function (&$field) {
            if (!empty($field->attribute)) {
                $field->resolve([$field->attribute => setting($field->attribute)]);
            }
        });

        return $fields;
    }

    public function save(NovaRequest $request)
    {
        $fields = collect(NovaSettingsTool::getSettingsFields());

        $fields->whereInstanceOf(Resolvable::class)->each(function ($field) use ($request) {
            if (empty($field->attribute)) return;

            $tempResource =  new \stdClass;
            $field->fill($request, $tempResource);

            setting([$field->attribute => $tempResource->{$field->attribute} ]);
        });

        setting()->save();

        return response('', 204);
    }
}
