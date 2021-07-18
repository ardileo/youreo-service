<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorBuilder;
use App\Helpers\Galileyo;
use App\Helpers\ImageBanker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FilesController extends Controller
{
    public function getFile(Request $request, $fieDir = null, $fileId = null)
    {

        if ($fileId != null) {
            $fileExt = pathinfo($fileId, PATHINFO_EXTENSION);
            $fileName = pathinfo($fileId, PATHINFO_FILENAME);

            return ImageBanker::findById($fileName);
        }
        return abort(404, 'File tidak ditemukan');
    }
}
