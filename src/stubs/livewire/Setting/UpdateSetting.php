<?php

namespace App\Livewire\Setting;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

class UpdateSetting extends Component
{

    /**
     * Todo: All of your public variables for storing data will be here on top
     *
     * ? Your Component Related variable for storing data
     */
    public $_data = [];
    public $opened = false;

    // Single Input
    public $keyid;
    public $keyvalue;

    /**
     * Todo: Mounted Data
     *
     * ? This method is used to mount data to the component
     */
    public function mount()
    {
        // Mount previous data from session
    }

    /**
     * Method is used to render the view/page visible via browser.
     */
    public function render()
    {

        // Ruturn the view component
        return view('livewire.setting.update-setting', [
            'found' => $this->_data
        ]);
    }

    /* --------------------------------------------------------------------------------------------- */

    /**
     * Todo: Listeing to the event from the Parent Component
     * ? Form Clicked Event
     */
    #[On('edit-initiated')]
    public function editInitiated($_id)
    {
        // If Null or Empty set  to null
        if (empty($_id) || is_null($_id)) {
            $this->opened = false;
            return;
        }

        // Todo: Search
        $_found = \App\Models\Vrm\Setting::where('id', $_id)->first();

        //  Process the data for edit
        if (!is_null($_found)) {
            $this->_data = $_found;
            $this->opened = true;

            // Assign the data to the public variable
            $this->keyid = $_found->id;
            $this->keyvalue = $_found->value;
        }
    }

    /**
     * Todo: Update Setting
     */
    public function updateSettingValue()
    {
        // Validate Data

        // Reset Edit
        $this->opened = false;

        // Values
        $_this_row = \App\Models\Vrm\Setting::where('id', $this->keyid)->first();
        if ($_this_row) {
            $_this_row->value = $this->keyvalue;
            $_this_row->save();

            // Notification
            session()->flash('notification', 'success');

            // Success Update
            $this->dispatch('livesetting-notify', ['status' => 'success', 'message' => 'Setting Updated Successfully']);
            return;
        }

        // Notification
        session()->flash('notification', 'error');

        // Failed Update
        $this->dispatch('livesetting-notify', ['status' => 'error', 'message' => 'Setting Update Failed']);
        return;
    }

    // Todo: Reset Edit
    #[On('reset-edit-initiated')]
    public function resetEditInitiated()
    {
        // Reset Results to hide the search results section
        $this->opened = false;
        $this->dispatch('clear-initiated');
    }
}
