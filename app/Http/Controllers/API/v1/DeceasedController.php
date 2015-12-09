<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Response;

use App\Deceased;

class DeceasedController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // sorting
        $sort_by    =   $request->has('sort_by') ? $request->input('sort_by') : 'created_at' ;
        $sort       =   $request->has('sort') ? $request->input('sort') : 'desc' ;

        // pagination
        $per_page    =   $request->has('per_page') ? $request->input('per_page') : 10 ;
        
        return Deceased::orderBy($sort_by, $sort)
                ->paginate($per_page);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return Deceased::Create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Deceased::findOrFail($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return Response::json(Deceased::findOrFail($id)->update(['name' => $request->input('name')]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deceased = Deceased::findOrFail($id);
        return $deceased->delete();
    }
}
