<?php

namespace App\Http\Controllers\Admin;

use File;
use Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use App\Repositories\Interfaces\Admin\MediaInterface;

class MediaController extends Controller
{
    protected $medias;

    public function __construct(MediaInterface $medias)
    {
        $this->medias = $medias;
    }

    public function index(Request $request)
    {
        $medias = $this->medias->paginate($request, get_pagination('media_paginate'));

        return view('admin.medias.index', compact('medias'));
    }

    public function getMedia(Request $request)
    {
        if ($request->get_data_for == 'all'):
            $medias = $this->medias->getAll();
        else:
            $type = explode(',', $request->get_data_for);
            $medias = $this->medias->getAll()->whereIn('type', $type);
        endif;
        if ($request->has('q')):
            $medias = $medias->where('name', 'like', '%' . $request->q . '%');
        endif;
        $selection = $request->selection;
        $data['total'] = $medias->count();
        $medias = $medias->limit(18)->get();

        $data['showing'] = $medias->count();

        $data['contents'] = view('admin.common.media-modal', compact('medias', 'selection'))->render();

        return response()->json($data);
    }

    public function getNewFiles(Request $request)
    {
        if ($request->get_data_for == 'all'):
            $medias = $this->medias->getAll();
        else:
            $type = explode(',', $request->get_data_for);
            $medias = $this->medias->getAll()->whereIn('type', $type);
        endif;
        $selection = $request->selection;

        $data['total'] = $medias->count();
        $medias = $medias->limit(18)->get();

        $data['showing'] = $medias->count();

        $data['contents'] = view('admin.common.new-medias', compact('medias', 'selection'))->render();

        return response()->json($data);
    }

    public function getMoreMedia(Request $request)
    {
        $medias = $this->medias->getAll()->where('type', $request->get_data_for);
        $selection = $request->selection;
        $showing = $request->showing;

        $data['total'] = $medias->count();
        $medias = $medias->skip($showing)->limit(18)->get();

        $data['showing'] = $showing + $medias->count();

        $data['contents'] = view('admin.common.new-medias', compact('medias', 'selection'))->render();

        return response()->json($data);
    }

    public function getSelectedMedia(Request $request)
    {
        $medias = $request->selected_medias ? $this->medias->getAll()->whereIn('id', $request->selected_medias)->get() : [];
        $selection = $request->selection;
        $type = $request->data_for;
        return view('admin.common.selected-medias', compact('medias', 'selection', 'type'))->render();
    }

    public function create()
    {
        return view('admin.medias.create');
    }

    public function store(Request $request)
    {
        if (config('app.demo_mode')):
            return response()->json(__('This function is disabled in demo server.'), 500);
        endif;

        $request->validate([
            'file' => 'required',
        ]);

        try {
            $type = get_yrsetting('supported_mimes');
            $extension = strtolower($request->file('file')->getClientOriginalExtension());
            $name = strtolower($request->file('file')->getClientOriginalName());
            $response = $this->medias->store($request->file('file'), ($type[$extension]));
            if ($response === false) {
                return response()->json(__('Unable to upload' . ' ' . $name), 500);
            } elseif ($response === 's3_error') {
                if (Sentinel::getUser()->user_type == 'seller') {
                    return response()->json(__('Unable to upload, please contact with system owner'), 500);
                } else {
                    return response()->json(__('Unable to upload to S3, check your configuration'), 500);
                }
            }
            return true;
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function delete($id)
    {
        if (config('app.demo_mode')):
            $response['message'] = __('This function is disabled in demo server.');
            $response['title'] = __('Ops..!');
            $response['status'] = 'error';
            return response()->json($response);
        endif;
        if (blank($this->medias->get($id))):
            $success['message'] = __('Not Found');
            $success['status'] = 'error';
            $success['title'] = __('404');
            return response()->json($success);
        endif;
        DB::beginTransaction();
        try {
            $this->medias->delete($id);
            $success['message'] = __('Deleted Successfully!');
            $success['status'] = 'success';
            $success['title'] = __('Deleted');
            DB::commit();
            return response()->json($success);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    public function details(Request $request)
    {
        $media = $this->medias->get($request->id);
        if (!blank($media)):
            $title = 'file_info';
            return view('admin.common.modal', compact('media', 'title'))->render();
        else:
            Toastr::error(__('Something went wrong, please try again'));
            return back();
        endif;
    }

    public function sellerBannerstore($file, $token_id)
    {
        if (config('app.demo_mode')):
            return response()->json(__('This function is disabled in demo server.'), 500);
        endif;

        $type = get_yrsetting('supported_mimes');
        if ($file) {
            $extension = strtolower($file->getClientOriginalExtension());
            $name = strtolower($file->getClientOriginalName());

            if (isset($type[$extension])):
                $response = $this->medias->store($file, ($type[$extension]), $token_id);
                if ($response === false):
                    return response()->json(__('Unable to upload' . ' ' . $name), 500);
                elseif ($response === 's3_error'):
                    if (Sentinel::getUser()->user_type == 'seller'):
                        return response()->json(__('Unable to upload, please contact with system owner'), 500);
                    else:
                        return response()->json(__('Unable to upload to S3, check your configuration'), 500);
                    endif;
                endif;
                return $response;
            endif;
        }
    }
}
