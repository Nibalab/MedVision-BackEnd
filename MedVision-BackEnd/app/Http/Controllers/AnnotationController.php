<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AnnotationController extends Controller
{
    public function index()
    {
        $annotations = Annotation::all();
        return response()->json($annotations);
    }

    public function store(Request $request)
    {
        $request->validate([
            'model_id' => 'required|exists:three_d_models,id',
            'doctor_id' => 'required|exists:users,id',
            'content' => 'required|string',
            'position' => 'nullable|string',
        ]);

        $annotation = Annotation::create($request->all());

        return response()->json($annotation, 201);
    }

    public function show($id)
    {
        $annotation = Annotation::findOrFail($id);
        return response()->json($annotation);
    }

    public function destroy($id)
    {
        $annotation = Annotation::findOrFail($id);
        $annotation->delete();
        return response()->json(['message' => 'Annotation deleted successfully']);
    }
}
