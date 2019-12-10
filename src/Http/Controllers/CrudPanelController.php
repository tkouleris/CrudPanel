<?php

namespace tkouleris\CrudPanel\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use tkouleris\CrudPanel\Models\MigrationFile;
use tkouleris\CrudPanel\Models\ModelFile;

class CrudPanelController extends Controller
{
    // View Controllers
    public function index()
    {
        $modelFiles = ModelFile::all()->take(5);
        return view('CrudPanel::crud_panel_index',compact('modelFiles'));
    }

    public function modelsIndex()
    {
        return view('CrudPanel::crud_panel_models');
    }

    // Other Requests
    public function create_model(Request $request, ModelFile $mf,MigrationFile $migf)
    {
        if(($request->model_name == null) || (!$request->has('model_name')) )
        {
            $results['success'] = false;
            $results['message'] = "No model name selected!";
            return $results;
        }

        if ($request->model_name == trim($request->model_name) && strpos($request->model_name, ' ') !== false) {
            $results['success'] = false;
            $results['message'] = "Model name must not contain spaces!";
            return $results;
        }

        $model = $mf::where('ModelFileName',$request->model_name)->first();

        if( $model == null)
        {
            $data = [
                'ModelFileName' =>$request->model_name
            ];
            $model_record = $mf::create($data);
        }

        Artisan::call('make:model '.$request->model_name);
        $message = Artisan::output();

        if( $request->create_migration == 1)
        {
            Artisan::call('make:migration create_'.$request->model_name.'_table');
            $MigrationOutput = Artisan::output();
            $MigrationFile = trim(substr($MigrationOutput,19));

            $ins_migration_args = array();
            $ins_migration_args['MigrationFileName'] = $MigrationFile;
            $ins_migration_args['MigrationModelId'] = $model_record->ModelFileId;
            $migration_record = $migf::create($ins_migration_args);

            $message .= $MigrationOutput;
        }

        if( $request->create_controller == 1)
        {
            Artisan::call('make:controller '.$request->model_name.'Controller');
            $message .= Artisan::output();
        }


        $results['success'] = true;
        $results['message'] = $message;
        return $results;
    }
}
