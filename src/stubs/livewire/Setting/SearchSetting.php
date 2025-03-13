<?php

namespace App\Livewire\Setting;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

class SearchSetting extends Component
{

    /**
     * Todo: All of your public variables for storing data will be here on top
     *
     * ? Your Component Related variable for storing data
     */
    public $_data;
    public $searchResults = [];

    /**
     * Method is used to render the view/page visible via browser.
     */
    public function render()
    {

        // Ruturn the view component
        return view('livewire.setting.search-setting', [
            'results' => $this->searchResults
        ]);
    }

    /* --------------------------------------------------------------------------------------------- */

    /**
     * Todo: Listeing to the event from the Parent Component
     * ? Receive the data from the parent component
     */
    #[On('search-initiated')]
    public function searchInitiated($_search)
    {
        // If Null or Empty set  to null
        if (empty($_search) || is_null($_search)) {
            $this->searchResults = null;
            return;
        }

        // Todo: Search
        $_found_results = \App\Models\Vrm\Setting::where('title', 'like', '%' . $_search . '%')->get();
        // Assign the data to the public variable
        $this->searchResults = (!$_found_results->isEmpty()) ? $_found_results : null;
    }


    // Todo: Edit Setting
    public function editSetting($_id)
    {
        // Reset Results to hide the search results section
        $this->searchResults = null;

        // Dispatch event to trigger edit component
        $this->dispatch('edit-initiated', $_id);
    }
}
