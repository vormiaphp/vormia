<?php

namespace App\Http\Controllers\Admin;

use App\Models\Role;
use App\Models\Vrm\Notify;
use App\Models\Vrm\Setting;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\User as User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    // PRIVATE VARIABLES
    private $Table = ''; // Table name will be pluralized

    private $ThemePath = ""; //Main Theme Path starting from resources/views/
    private $MainFolder = "vrms"; //Main Folder Name (in prural) inside the resources/views/$ThemePath/pages
    private $SubFolder = "/users"; //Sub Folder Name inside the resources/views/$ThemePath/pages/$MainFolder/
    private $Upload = ""; //Upload Folder Name inside the public/admin/media

    private $ParentRoute = "vrm/users"; // Parent Route Name Eg. vrm-settings
    private $AllowedFile = null; //Set Default allowed file extension, remember you can pass this upon upload to override default allowed file type. jpg|jpeg|png|doc|docx|

    private $New = 'vrm/users/add'; // New
    private $Save = 'vrm/users/save'; // Add New
    private $Edit = 'vrm/users/edit'; // Edit
    private $Update = 'vrm/users/update'; // Update
    private $Delete = 'vrm/users/delete'; // Delete
    private $Action = 'vrm/users/status'; // Multiple Entry Action

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

        // Roles
        $setting['main_roles'] = Role::where('authority', 'main')->get();

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

        // User List
        $role_select = $data['main_roles']->pluck('id')->toArray();
        $data['user_list'] = User::whereHas('roles', function ($query) use ($role_select) {
            $query->whereIn('role_id', $role_select);
        })->with('roles')->latest()->get();

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
        $page = Str::plural($this->MainFolder) . "/" . $this->SubFolder . "/$view";

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

        // ? Fetch customer
        $customerInfo = User::with(['usermetas', 'roles'])->where('id', $id)->first();

        if (is_null($customerInfo)) {
            // Notification
            session()->flash('notification', 'error');
            // Open Page
            return $this->index('<strong>Error:</strong> Invalid request, please try again.');
        }

        // Data Found
        $data['resultFound'] = $customerInfo;

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
            $user = User::where('id', $id)->first();

            // ? Check if the hierarchy exists
            if (is_null($user)) {
                // Notification
                session()->flash('notification', 'error');

                // Open Page
                return $this->index('<strong>Error:</strong> Invalid request, please try again.');
            }

            // ? Update the hierarchy
            $user->update(['flag' => 1]);

            // Notification
            session()->flash('notification', 'success');

            // Open Page
            return $this->index('<strong>Success:</strong> User was activated successfully.');
        } elseif ($action == 'deactivate') {
            // ? Get the id
            $id = $request->get('id');

            // ? Select Hierarchy
            $user = User::where('id', $id)->first();

            // ? Check if the hierarchy exists
            if (is_null($user)) {
                // Notification
                session()->flash('notification', 'error');

                // Open Page
                return $this->index('<strong>Error:</strong> Invalid request, please try again.');
            }

            // ? Update the hierarchy
            $user->update(['flag' => 0]);

            // Notification
            session()->flash('notification', 'success');

            // Open Page
            return $this->index('<strong>Success:</strong> User was deactivated successfully.');
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
            'email' => "required|max:200|email|unique:users,email",
            'username' => "required|max:20|unique:users,username",
            'access' => "required|array",
            // 'phone' => "nullable|between:8,17|unique:users,phone|regex:/^\d{1,3}\d{9,}$/",
            'phone' => [
                'nullable',
                'between:8,17',
                'regex:/^\d{1,3}\d{9,}$/',
                Rule::unique('users', 'phone')->whereNotNull('phone'),
            ],
            fn($attribute, $value, $fail) => strlen($value) > 11 ? $fail('The ' . $attribute . ' field must not be greater than 11.') : null,
            'password' => "required|min:5|max:20|confirmed",
        ], [
            'phone.regex' => 'The phone number must be in international format with country code, e.g. "254xxxxxxxxxx".',
        ]);

        // On Validation Failphp
        if ($validator->fails()) {
            session()->flash('notification', 'error');
            Notify::error('Please check the form for errors.');

            // Return Error Message
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        /**
         * Todo: Check if the phone number is already in use
         */
        //? Remove + from phone number
        $user_mobile = Str::replaceFirst('+', '', $request->get('phone'));

        // ? Select from User where phone = $user_mobile or phone = $request->get('user_mobile')
        $existing = User::where('phone', $user_mobile)->orWhere('phone', $request->get('phone'))->first()?->phone;

        // Check Email
        if (!is_null($existing)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'phone' => 'The mobile number is already in use',
            ]);
        }
        /* End */

        // Validate Form Data
        $validated = $validator->validated();

        // Remove + from phone number
        $validated['phone'] = $user_mobile;

        // Add User Role
        $insertFrom['name'] = $validated['name'];
        $insertFrom['email'] = $validated['email'];
        $insertFrom['password'] = Hash::make($validated['password']);
        $insertFrom['phone'] = (!empty($validated['phone']) && !is_null($validated['phone'])) ? $validated['phone'] : null;

        // usermeta
        $insertFrom['usermeta'] = [
            'profile' => null,
        ];

        // Username
        $insertFrom['username'] = \App\Models\Vrm\Term::username($validated['username']);
        // Unset Data
        $save = Arr::except($insertFrom, []);

        // Check if key usermeta exists, if yes assign the data to another array the unset the key
        if (array_key_exists('usermeta', $save)) {
            $usermeta = $save['usermeta'];
            unset($save['usermeta']);
        }

        // Save Form Data
        $saved = User::create($save);
        if ($saved) {
            // Add to Usermeta
            if (isset($usermeta)) {
                // Call Usermeta Model
                User::save_usermeta($saved->id, $usermeta);
            }

            // Roles
            $roles = $validated['access'];
            User::find($saved->id)->roles()->attach($roles);

            // Notification
            session()->flash('notification', 'success');

            // Open Page
            return $this->index('<strong>Error:</strong> Account creation was successful.');
        } else {
            // Notification
            session()->flash('notification', 'error');

            // Open Page
            return redirect()->back()->with('message', '<strong>Error:</strong> Customer Account was not created, kindly try again.');
        }
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
        $userId = $request->user_id;
        $allowed_files = (is_null($this->AllowedFile)) ? 'jpg,jpeg,png' : $this->AllowedFile; //Set Allowed Files
        $upoadDirectory = $this->Upload . "/"; //Upload Location

        // Validate Form Data
        $validator = Validator::make($request->all(), [
            'name' => "required|max:100",
            'access' => "required|array",
            'email' => "required|max:200|email|unique:users,email,$userId",
            // 'phone' => "nullable|between:8,17|regex:/^\d{1,3}\d{9,}$/|unique:users,phone,$userId",
            'phone' => [
                'nullable',
                'between:8,17',
                'regex:/^\d{1,3}\d{9,}$/',
                Rule::unique('users', 'phone')->ignore($userId)->whereNotNull('phone'),
            ],
            fn($attribute, $value, $fail) => strlen($value) > 11 ? $fail('The ' . $attribute . ' field must not be greater than 11.') : null,
            'password' => "|max:20|confirmed",
            'note' => "nullable|max:300",
        ], [
            'phone.regex' => 'The phone number must be in international format with country code, e.g. "254xxxxxxxxxx".',
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

        // Remove + from phone number
        $validated['phone'] = Str::replaceFirst('+', '', $validated['phone']);

        // Add User Role
        $updateFrom['name'] = $validated['name'];
        $updateFrom['email'] = $validated['email'];
        // if password is not empty
        if (!empty($validated['password']) && !is_null($validated['password'])) {
            $updateFrom['password'] = Hash::make($validated['password']);
        }
        // $updateFrom['phone'] = $validated['phone'];
        $updateFrom['phone'] = (!empty($validated['phone']) && !is_null($validated['phone'])) ? $validated['phone'] : null;

        // usermeta
        $updateFrommeta = [
            'profile' => null,
            'note' => $validated['note'],
        ];

        // Store Product
        DB::beginTransaction();

        try {
            $user = User::with('usermetas')->where('id', $userId)->first(); // Assuming you have the product ID Update the product data
            $user->update($updateFrom);

            foreach ($updateFrommeta as $key => $value) {
                if (!in_array($key, ['id', 'created_at', 'updated_at'])) {
                    // loop through the array $product->productmetas() and update the meta_value
                    $user->usermetas()->updateOrCreate(
                        ['key' => $key],
                        ['value' => $value]
                    );
                }
            }

            // Roles
            $roles = $validated['access'];
            User::find($userId)->roles()->sync($roles);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;

            // Notification
            session()->flash('notification', 'error');

            // Open Page
            return redirect()->back()->with('message', 'DB-Error! Customer info could not be updated.');
        }
        // Notification
        session()->flash('notification', 'success');

        // Open Page
        return redirect()->back()->with('message', 'Customer Info was Updated successfully.');
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
            if (User::where('id', $id)->delete()) {
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
        if (User::where('id', $id)->delete()) {
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
