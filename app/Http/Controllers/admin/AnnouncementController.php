<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Services\AdminSettingService;
use App\Http\Services\Logger;
use App\Model\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AnnouncementController extends Controller
{
    public $settingService;
    public $logger;
    public function __construct()
    {
        $this->settingService = new AdminSettingService();
        $this->logger = new Logger();
    }

    //  List
    public function adminAnnouncementList(Request $request)
    {
        $data['title'] = __('Landing Announcement');
        if ($request->ajax()) {
            $data['items'] = Announcement::orderBy('id', 'desc');
            return datatables()->of($data['items'])
                ->addColumn('status', function ($item) {
                    return status($item->status);
                })
                ->editColumn('created_at', function ($item) {
                    return $item->created_at;
                })
                ->addColumn('actions', function ($item) {
                    return '<ul class="d-flex activity-menu">
                        <li class="viewuser"><a href="' . route('adminAnnouncementEdit', $item->id) . '"><i class="fa fa-pencil"></i></a> </li>
                        <li class="deleteuser"><a href="' . route('adminAnnouncementDelete', $item->id) . '"><i class="fa fa-trash"></i></a></li>
                        </ul>';
                })
                ->rawColumns(['actions','status'])
                ->make(true);
        }

        return view('admin.announcement.list', $data);
    }

    // View Add new page
    public function adminAnnouncementAdd()
    {
        $data['title']=__('Add Announcement');
        $data['button_title']=__('Add Announcement');
        return view('admin.announcement.addEdit',$data);
    }

    // Create New
    public function adminAnnouncementSave(Request $request)
    {
        try {
            $rules = [
                'title'=>'required',
                'details'=>'required',
                'status'=>'required',
            ];
            if(!empty($request->image)){
                $rules['image']='image|mimes:jpg,jpeg,png,PNG,JPG,JPEG|max:2024';
            }
            $messages = [
                'title.required' => __('Title field can not be empty'),
                'details.required' => __('Details field can not be empty'),
                'status.required' => __('Status field can not be empty'),
            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $errors = [];
                $e = $validator->errors()->all();
                foreach ($e as $error) {
                    $errors[] = $error;
                }
                return redirect()->back()->withInput()->with(['dismiss' => $errors[0]]);
            }

            $response = $this->settingService->saveAnnouncement($request);
            if ($response['success'] == true) {
                return redirect()->route('adminAnnouncementList')->with(['success'=> $response['message']]);
            } else {
                return redirect()->back()->with(['dismiss'=> $response['message']]);
            }
        } catch (\Exception $e) {
            $this->logger->log('adminAnnouncementSave', $e->getMessage());
            return redirect()->back()->with('dismiss', __('Something went wrong'));
        }
    }

    // Edit
    public function adminAnnouncementEdit($id)
    {
        $data['title'] = __('Update Announcement');
        $data['button_title'] = __('Update Announcement');
        $data['item'] = Announcement::findOrFail($id);

        return view('admin.announcement.addEdit',$data);
    }

    // Delete
    public function adminAnnouncementDelete($id)
    {
        if(isset($id)) {
            Announcement::where(['id'=>$id])->delete();
        }
        return redirect()->back()->with(['success'=>__('Deleted Successfully!')]);
    }
}
