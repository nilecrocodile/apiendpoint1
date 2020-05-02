<?php

namespace App\Http\Controllers;

use App\CalorieBalance;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiController extends Controller
{

    private function checkAuthToken(Request $request) {
        $authToken = $request->get('auth-token');

        if (empty($authToken)) {
            throw new \Exception("Unauthorized");
        }

        $user = User::where(['auth_token' => $request->get('auth-token')])->first();

        if (empty($user)) {
            throw new \Exception("Unauthorized");
        }
    }

    public function getBalance(Request $request) {

        $this->checkAuthToken($request);

        $user = User::where(['auth_token' => $request->get('auth-token')])->first();

        if (empty($user)) {
            throw new \Exception("Unauthorized");
        }

        $totalBurned = CalorieBalance::where(['type' => CalorieBalance::TYPE_BURNED])->sum('count');
        $totalIntaken = CalorieBalance::where(['type' => CalorieBalance::TYPE_INTAKEN])->sum('count');

        return response()->json([
            'status' => 'success',
            'total_burned' => $totalBurned,
            'total_intaken' => $totalIntaken
        ]);

    }

    public function getItems(Request $request) {

        $this->checkAuthToken($request);

        $type = $request->get('type');

        if ($type != CalorieBalance::TYPE_INTAKEN && $type != CalorieBalance::TYPE_BURNED) {
            throw new \Exception("Unknown type");
        }

        $result = CalorieBalance::where(['type' => $type])->get();

        return response()->json([
            'status' => 'success',
            'data' => $result
        ]);

    }

    public function addItem(Request $request) {

        $this->checkAuthToken($request);

        if ($request->get('type') != CalorieBalance::TYPE_INTAKEN && $request->get('type') != CalorieBalance::TYPE_BURNED) {
            throw new \Exception("Unknown type");
        }

        if (empty($request->get('name'))) {
            throw new \Exception("Wrong name");
        }

        $newItem = new CalorieBalance();

        $newItem->name = $request->get('name');
        $newItem->type = $request->get('type');
        $newItem->count = (int)$request->get('count');
        $newItem->date = new \DateTime();

        $newItem->saveOrFail();

        $newItem->refresh();

        return response()->json([
            'status' => 'success',
            'id' => $newItem->id
        ]);

    }

    public function removeItem(Request $request) {

        $this->checkAuthToken($request);

        $id = $request->get('id');

        $item = CalorieBalance::find($id);

        if (!empty($item)) {
            $item->delete();
        } else {
            throw new \Exception("Item not found");
        }

        return response()->json([
            'status' => 'success',
            'id' => $id
        ]);

    }


}
