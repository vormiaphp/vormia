<?php

namespace App\Services\Vrm;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Request;

class GlobalDataService
{
    /**
     * Get global data
     *
     * @return object
     */
    public function __invoke(): object
    {
        return (object) array_merge(
            $this->staticData(),
            $this->dynamicData()
        );
    }

    /**
     * Get static data
     *
     * @return array
     */
    protected function staticData(): array
    {
        return  [
            'base_url' => URL::to('/'),
            'theme_dir'    => 'themes/' . app('vrm.utilities')->type('public')->get('theme'),
            'theme_asset'    => 'content/themes/' . app('vrm.utilities')->type('public')->get('theme'),
            'theme_component' => 'themes/' . app('vrm.utilities')->type('public')->get('theme') . '/components',
        ];
    }

    /**
     * Get dynamic data
     *
     * @return array
     */
    protected function dynamicData(): array
    {

        $breadcrumb = [];
        $this_page  = Request::path();
        $curr_menu  = '';

        return [
            'breadcrumb' => $breadcrumb,
            'this_page'  => $this_page,
            'curr_menu'  => $curr_menu,
            'timezone'   => config('app.timezone'),
        ];
    }
}
