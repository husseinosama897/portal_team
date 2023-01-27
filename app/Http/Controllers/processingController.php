<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Writer\HTML;
class processingController extends Controller
{
    public function addfile(request $request){
        $word = IOFactory::load($request->file);
        $html = new HTML($word);
        $html->save('content.html');
        $content = readfile('content.html');
        Storage::delete('content.html');

        return response()->json(['data'=>$content]);
    }
}
