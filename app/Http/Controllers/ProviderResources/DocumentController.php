<?php

namespace App\Http\Controllers\ProviderResources;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Document;
use App\ProviderDocument;
use App\Provider;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $DriverDocuments = Document::driver()->get();

        
        foreach ($DriverDocuments as $key => $value) {
            $Document = null;
            if(\Auth::user()){
                $Document = ProviderDocument::where('provider_id', \Auth::user()->id)
                ->where('document_id', $value->id)
                ->first();
            }
            $DriverDocuments[$key]['document'] = $Document;
        }

        if($request->ajax()){
            return response()->json(['message' => 'Document Listed','data'=>$DriverDocuments], 200);
        }
        else{
            $Provider = \Auth::guard('provider')->user();
            return view('provider.document.index', compact('DriverDocuments', 'Provider'));
        }
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        $this->validate($request, [
                'document' => 'mimes:jpg,jpeg,png,pdf',
            ]);

        try {
            
            $Document = ProviderDocument::where('provider_id', \Auth::guard('provider')->user()->id)
                ->where('document_id', $id)
                ->firstOrFail();

            $Document->update([
                    'url' => $request->document->store('provider/documents'),
                    'status' => 'ASSESSING',
                ]);

            //update document to card status
            $total = Document::count();
            $provider_total = ProviderDocument::where('provider_id', \Auth::guard('provider')->user()->id)->count();
           
            if($total==$provider_total){
                Provider::where('id',\Auth::guard('provider')->user()->id)->where('status','document')->update(['status'=>'approved']);   
            }

            return back();

        } catch (ModelNotFoundException $e) {

            ProviderDocument::create([
                    'url' => $request->document->store('provider/documents'),
                    'provider_id' => \Auth::guard('provider')->user()->id,
                    'document_id' => $id,
                    'status' => 'ASSESSING',
                ]);

            //update document to card status
            $total = Document::count();
            $provider_total = ProviderDocument::where('provider_id', \Auth::guard('provider')->user()->id)->count();
           
            if($total==$provider_total){
                Provider::where('id',\Auth::guard('provider')->user()->id)->where('status','document')->update(['status'=>'approved']);   
            }
            
        }

        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateApi(Request $request)
    {
        \Log::info('ss');
        \Log::info($request->all());
        $this->validate($request, [
            'document' => 'required|mimes:jpg,jpeg,png,pdf',
            'document_id' => 'required'
        ]);
        \Log::info('ss1');
        $id = $request->document_id;
        try {
            
            $Document = ProviderDocument::where('provider_id', \Auth::user()->id)
                ->where('document_id', $id)
                ->firstOrFail();

            $Document->update([
                    'url' => $request->document->store('provider/documents'),
                    'status' => 'ASSESSING',
                ]);

            //update document to card status
            $total = Document::count();
            $provider_total = ProviderDocument::where('provider_id', \Auth::user()->id)->count();
           
            if($total==$provider_total){
                Provider::where('id',\Auth::user()->id)->where('status','document')->update(['status'=>'approved']);   
            }

            return response()->json(['message' => 'Document Updated','data'=>$Document], 200);

        } catch (ModelNotFoundException $e) {

            $Document = ProviderDocument::create([
                    'url' => $request->document->store('provider/documents'),
                    'provider_id' => \Auth::user()->id,
                    'document_id' => $id,
                    'status' => 'ASSESSING',
                ]);
            //update document to card status
            $total = Document::count();
            $provider_total = ProviderDocument::where('provider_id', \Auth::user()->id)->count();
           
            if($total==$provider_total){
                Provider::where('id',\Auth::user()->id)->where('status','document')->update(['status'=>'approved']);   
            }
            
        }        

        return response()->json(['message' => 'Document Created','data'=>$Document], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
