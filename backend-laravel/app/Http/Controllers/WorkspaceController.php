<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
class WorkspaceController extends Controller
{
 public function index(): View { return view('dashboard'); }
 public function health() { return response()->json(Http::timeout(5)->get(config('services.ai_engine.url').'/health')->json()); }
 public function analytics() { return response()->json(Http::timeout(5)->get(config('services.ai_engine.url').'/analytics')->json()); }
 public function normalize(Request $request) {
  $request->validate(['file'=>'required|file|max:51200']);
  $response=Http::timeout(120)->attach('file',fopen($request->file('file')->getRealPath(),'r'),$request->file('file')->getClientOriginalName())->post(config('services.ai_engine.url').'/normalize');
  return response()->json($response->json(),$response->status());
 }
 public function search(Request $request) {
  $data=$request->validate(['query'=>'required|string|max:500','limit'=>'nullable|integer|min:1|max:20']);
  $response=Http::timeout(30)->post(config('services.ai_engine.url').'/search',$data);
  return response()->json($response->json(),$response->status());
 }
}
