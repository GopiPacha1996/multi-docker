<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Pu_OauthClients;

class ClientDetailsController extends Controller
{
    public function index(Request $request)
    {
        $clients = Pu_OauthClients::select('id','name')
                    ->where('revoked',0)
                    ->where('issue_status','2');

        $pageCount = isset($request->PageCount)?$request->PageCount:10;
        if($request->search != NULL)
        {
            $clients = $clients->where('name','like','%'.$request->search.'%')
                        ->orWhere('id',$request->search);
        }

        $clients = $clients->paginate($pageCount);
        return $this->simpleReturn('success', $clients);
    }

}
