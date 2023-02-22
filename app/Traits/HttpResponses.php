<?php

namespace App\Traits;


trait HttpResponses{
	protected function sendResponse($res, $code = 200){
		return response()->json([ 'message' => $res ], $code);
	}

	protected function sendData($data, $code = 200){
		return response()->json($data, $code);
	}

	protected function sendError($err, $code = 400){
		return response()->json([ 'err' => $err], $code);
	}
}

?>