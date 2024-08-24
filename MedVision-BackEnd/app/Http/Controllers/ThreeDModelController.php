<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ThreeDModelController extends Controller
{
    public function index()
    {
        $models = ThreeDModel::all();
        return response()->json($models);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ct_scan_id' => 'required|exists:ct_scans,id',
            'model_path' => 'required|string',
        ]);

        $model = ThreeDModel::create($request->all());

        return response()->json($model, 201);
    }

    public function show($id)
    {
        $model = ThreeDModel::findOrFail($id);
        return response()->json($model);
    }

    public function destroy($id)
    {
        $model = ThreeDModel::findOrFail($id);
        $model->delete();
        return response()->json(['message' => '3D Model deleted successfully']);
    }
}
