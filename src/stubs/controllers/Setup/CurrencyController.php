<?php

namespace App\Http\Controllers\Setup;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Vrm\HierarchyMeta;
use App\Models\Vrm\Hierarchy;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use App\Models\Vrm\Setting;
use App\Models\Vrm\Notify;

class CurrencyController extends Controller
{

    // PRIVATE VARIABLES
    private $Table = ''; // Table name will be pluralized

    private $ThemePath = ""; //Main Theme Path starting from resources/views/
    private $MainFolder = "setups"; //Main Folder Name (in prural) inside the resources/views/$ThemePath/pages
    private $SubFolder = "/currency"; //Sub Folder Name inside the resources/views/$ThemePath/pages/$MainFolder/
    private $Upload = ""; //Upload Folder Name inside the public/admin/media

    private $ParentRoute = "vrm/setup/currency"; // Parent Route Name Eg. vrm-settings
    private $AllowedFile = null; //Set Default allowed file extension, remember you can pass this upon upload to override default allowed file type. jpg|jpeg|png|doc|docx|

    private $New = ''; // New
    private $Save = 'vrm/setup/currency/save'; // Add New
    private $Edit = 'vrm/setup/currency/edit'; // Edit
    private $Update = 'vrm/setup/currency/update'; // Update
    private $Delete = 'vrm/setup/currency/delete'; // Delete
    private $Action = 'vrm/setup/currency/status'; // Multiple Entry Action

    private $HeaderName = ""; // (Optional) Name

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //

    }

    /**
     * Global Settings {loadSettings}
     * Method is private and not accessible via the web
     * Todo: This method Load all settings from database via the PreLoad Model:: getSettings()
     *
     * @param optional $view_name (string) Page Name (make sure to add $ThemePath/$MainFolder/$SubFolder/$page_name)
     *
     * @return \Illuminate\Http\Response
     */
    private function loadSettings($view_name = '')
    {
        // Load in Controller Settings from passedSettings method
        $passed = $this->passedSettings();

        //openLoad settings
        $settings = Setting::adminLoad($view_name, $passed);

        // Return all settings
        return $settings;
    }

    /**
     * Custom Settings {passedSettings}
     * Method is private and not accessible via the web
     * Todo: This method Load all settings for this Controller only
     *
     * @param optional $addtionalData (array) any additional data to be passed on demand
     *
     * @return \Illuminate\Http\Response
     */
    private function passedSettings($addtional_data = [])
    {
        date_default_timezone_set('Africa/Nairobi'); //Time Zone
        $setting['dateTime'] = strtotime(date('Y-m-d, H:i:s')); //Current DateTime

        // Links
        $setting['links'] = (object)[
            'new' => $this->New,
            'save' => $this->Save,
            'edit' => $this->Edit,
            'update' => $this->Update,
            'delete' => $this->Delete,
            'manage' => $this->Action,
            'route' => $this->ParentRoute,
        ];

        // Other
        $setting['other'] = (object)[
            'headerName' => (!array_key_exists('headerName', $addtional_data)) ? $this->HeaderName : $addtional_data['headerName'],
        ];

        // Header
        $setting['h4_pagetitle'] = 'Dashboard';
        $setting['breadcrumb'] = [];

        $setting['entry_list'] = Hierarchy::where('type', 'currency')->latest()->get();

        // Merge all settings into one array
        $setting = array_merge($setting, $addtional_data);

        // Return all settings
        return $setting;
    }

    /**
     * Page View {show}
     * Method is private and not accessible via the web
     * Todo: This method is the only method that is accessible render the view/page visible via browser.
     *
     * @param  requred $data - (has all the values needed to render the page)
     * @param  optional $layout - (By default the layout is main)
     *
     * @return \Illuminate\Http\Response
     */
    private function show($data, $layout = 'main')
    {
        // Add Layout
        $data['layoutName'] = $layout;
        //Load Page View
        return view("admin/pages/" . $data['page_name'], $data);
    }

    /**
     * Main {Index}
     * Method is public and accessible via the web
     * Todo: This method is the main settings page.
     *
     * @param  optional  $message - notification message (By default, no message is displayed)
     *
     * @return \Illuminate\Http\Response
     */
    public function index($message = '')
    {
        // Load View Page Path
        $view = 'list';
        $page = Str::plural($this->MainFolder) . $this->SubFolder .  "/$view";

        // Load Settings
        $data = $this->loadSettings($page);
        $data['other']->view = $view;

        //Notification
        $notify = Notify::notify();
        $data['notify'] = Notify::$notify($message);

        //Open Page
        return $this->show($data, 'list');
    }

    /**
     * Page {open}
     * Method is public and accessible via the web
     * Todo: This method is used to open a specific view/page (you can pass the view name/full_path and open will call show() method to render the view/page)
     *
     * @param required $view - (the view name/full_path to be rendered)
     * @param  optional $message - notification message (By default, no message is displayed)
     * @param  optional $layout - (By default the layout is main)
     *
     * @return \Illuminate\Http\Response
     */
    public function open($view, $message = '', $layout = 'main')
    {
        // Load View Page Path
        $page = Str::plural($this->MainFolder) . "/" . $this->SubFolder . $view;

        // Load Settings
        $data = $this->loadSettings($page);
        $data['other']->view = $view;

        //Notification
        $notify = Notify::notify();
        $data['notify'] = Notify::$notify($message);

        //Open Page
        return $this->show($data, $layout);
    }

    /**
     * Page {edit}
     * Method is private and can;t via the web
     * Todo: This method is used to preview/open for edit a record
     *
     * @param  \Illuminate\Http\Request  $request - (the request object)
     * @param  string $page - page to be opened by default is edit
     * @param  optional $message - notification message (By default, no message is displayed)
     * @param  optional $layout - (By default the layout is main)
     */
    public function edit(Request $request, $page = 'edit', $message = '', $layout = 'main')
    {
        // Load View Page Path
        $page = Str::plural($this->MainFolder) . $this->SubFolder .  "/$page";

        // Load Settings
        $data = $this->loadSettings($page);
        $data['other']->view = $page;

        // ? Get the id
        $id = $request->get('id');

        // ? Fetch Hierarchy
        $hierarchyInfo = Hierarchy::with('attributes')->where('id', $id)->first();

        if (is_null($hierarchyInfo)) {
            // Notification
            session()->flash('notification', 'error');
            // Open Page
            return $this->index('<strong>Error:</strong> Invalid request, please try again.');
        }

        // Data Found
        $data['resultFound'] = $hierarchyInfo;

        //Notification
        $notify = Notify::notify();
        $data['notify'] = Notify::$notify($message);

        //Open Page
        return $this->show($data, $layout);
    }

    /**
     * Validation {valid}
     * Method is public and accessible via the web
     * Todo: This method is used to validate the form data.
     *
     * @param  \Illuminate\Http\Request  $request - (the request object)
     * @param  required $action - (what option to validate)
     *
     * @return \Illuminate\Http\Response
     */
    public function valid(Request $request, $action = '')
    {

        $allowed_files = (is_null($this->AllowedFile)) ? 'jpg|jpeg|png|doc|docx|pdf|xls|txt' : $this->AllowedFile; //Set Allowed Files
        $upoadDirectory = $this->Upload . "/"; //Upload Location

        //Check Validation
        if ($action == 'activate') {
            // ? Get the id
            $id = $request->get('id');

            // ? Select Hierarchy
            $hierarchy = Hierarchy::with('attributes')->where('id', $id)->first();

            // ? Check if the hierarchy exists
            if (is_null($hierarchy)) {
                // Notification
                session()->flash('notification', 'error');

                // Open Page
                return $this->index('<strong>Error:</strong> Invalid request, please try again.');
            }

            // ? Update the hierarchy
            $hierarchy->update(['flag' => 1]);

            // Notification
            session()->flash('notification', 'success');

            // Open Page
            return $this->index('<strong>Success:</strong> Status was activated successfully.');
        } elseif ($action == 'deactivate') {
            // ? Get the id
            $id = $request->get('id');

            // ? Select Hierarchy
            $hierarchy = Hierarchy::with('attributes')->where('id', $id)->first();

            // ? Check if the hierarchy exists
            if (is_null($hierarchy)) {
                // Notification
                session()->flash('notification', 'error');

                // Open Page
                return $this->index('<strong>Error:</strong> Invalid request, please try again.');
            }

            // ? Update the hierarchy
            $hierarchy->update(['flag' => 0]);

            // Notification
            session()->flash('notification', 'success');

            // Open Page
            return $this->index('<strong>Success:</strong> Status was deactivated successfully.');
        } else {

            // Notification
            session()->flash('notification', 'info');

            // Open Page
            return $this->index('<strong>Info:</strong> Vormia failed to respond, unknown request.');
        }
    }

    /**
     * Todo: This method is used to register customer.
     *
     * @param  \Illuminate\Http\Request  $request - (the request object)
     * @param  required $action - (what option to validate)
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $action = '')
    {
        // Validate Form Data
        $validator = Validator::make($request->all(), [
            'name' => "required|max:100",
            'exchange' => "required|numeric",
            'note' => "nullable|max:300",
        ]);

        // On Validation Failphp
        if ($validator->fails()) {
            session()->flash('notification', 'error');
            Notify::error('Please check the form for errors.');

            // Return Error Message
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        // Validate Form Data
        $validated = $validator->validated();

        // Add Type
        $insertFrom['name'] = $validated['name'];
        $insertFrom['type'] = 'currency';
        $insertFrom['parent'] = 0;
        $insertFrom['flag'] = 1;

        // attributes
        $insertFrom['attributes'] = [
            'exchange' => $validated['exchange'],
            'note' => $validated['note'],
        ];

        // Unset Data
        $save = Arr::except($insertFrom, []);

        // Check if key attributes exists, if yes assign the data to another array the unset the key
        if (array_key_exists('attributes', $save)) {
            $attributes = $save['attributes'];
            unset($save['attributes']);
        }

        // Save Form Data
        $saved =  Hierarchy::create($save);
        if ($saved) {
            // Add to Attributes
            if (isset($attributes)) {
                // HierarchyMeta
                foreach ($insertFrom['attributes'] as $key => $value) {
                    $meta = new HierarchyMeta();

                    $meta->hierarchy = $saved->id;
                    $meta->key = $key;
                    $meta->value = $value;
                    $meta->save();
                }
            }

            // ? Term
            \App\Models\Vrm\Term::create([
                "table" => "hierarchy",
                "type" => null,
                "related" => $saved->id,
                "slug" => \App\Models\Vrm\Term::slug($saved->name),
                "flag" => 1,
            ]);

            // Notification
            session()->flash('notification', 'success');

            // Open Page
            return $this->index('<strong>Success:</strong> Status creationg was successful.');
        }
        // Notification
        session()->flash('notification', 'error');

        // Open Page
        return redirect()->back()->with('message', '<strong>Error:</strong> Status was not created, kindly try again.');
    }

    /**
     * For Updating {update}
     * Method is private and not accessible via the web
     * Todo: This method is used to update data to the database.
     *
     * @param  \Illuminate\Http\Request  $request - (the request object)
     * @param  required $action - (what option to validate)
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $action = '')
    {
        $hierarchyId = $request->get('id');
        $allowed_files = (is_null($this->AllowedFile)) ? 'jpg,jpeg,png' : $this->AllowedFile; //Set Allowed Files
        $upoadDirectory = $this->Upload . "/"; //Upload Location

        // Validate Form Data
        $validator = Validator::make($request->all(), [
            'name' => "required|max:100",
            'exchange' => "required|numeric",
            'note' => "nullable|max:300",
        ]);

        // On Validation Failphp
        if ($validator->fails()) {
            session()->flash('notification', 'error');
            Notify::error('Please check the form for errors.');

            // Return Error Message
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        // Validate Form Data
        $validated = $validator->validated();

        // Update Hierarchy
        $updateFrom['name'] = $validated['name'];

        // attributes
        $attributes = [
            'exchange' => $validated['exchange'],
            'note' => $validated['note'],
        ];

        // Store Product
        DB::beginTransaction();

        try {
            $user = Hierarchy::with('attributes')->where('id', $hierarchyId)->first(); // Assuming you have the product ID Update the product data
            $user->update($updateFrom);

            foreach ($attributes as $key => $value) {
                if (!in_array($key, ['id', 'created_at', 'updated_at'])) {
                    // loop through the array $product->productmetas() and update the meta_value
                    $user->attributes()->updateOrCreate(
                        ['key' => $key],
                        ['value' => $value]
                    );
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;

            // Notification
            session()->flash('notification', 'error');

            // Open Page
            return redirect()->back()->with('message', 'DB-Error! Status info could not be updated.');
        }
        // Notification
        session()->flash('notification', 'success');

        // Open Page
        return redirect()->back()->with('message', 'Status Info was Updated successfully.');
    }

    /**
     * For Deleting {delete}
     * Method is public and accessible via the web
     *
     * Todo: This method is used to delete data from the database.
     *
     * @param  \Illuminate\Http\Request  $request - (the request object)
     * @param  required $action - (what option to validate)
     *
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request, $action = '')
    {
        // ? Check if the request is ajax
        if ($request->ajax()) {
            // ? Get the id
            $id = $request->get('id');

            // ? Check if the id is valid
            if (is_null($id)) {
                // ? Return error
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid request, please try again.'
                ]);
            }

            // ? Delete the record
            if (Hierarchy::where('id', $id)->delete()) {
                // ? Return success
                return response()->json([
                    'status' => 'success',
                    'message' => 'Record deleted successfully.'
                ]);
            }

            // ? Return error
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete, please try again.'
            ]);
        }

        // ? Check if is ID is in GET or POST
        $id = $request->get('id');

        // ? delete the record
        if (Hierarchy::where('id', $id)->delete()) {
            // Notification
            session()->flash('notification', 'success');

            // ? Return success
            return $this->index('<strong>Success:</strong> Record deleted successfully.');
        }

        // Notification
        session()->flash('notification', 'error');

        // ? Return error
        return $this->index('<strong>Error:</strong> Failed to delete, please try again.');
    }
}
