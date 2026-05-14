<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserAddressController extends Controller
{

    // get all client address
    public function index(Request $request)
    {
        return response()->json([
            'address' => $request->user()->address,
        ], 200);
    }


    public function store(Request $request)
    {

        // validate data
        $validated = $request->validate([
            'government_id' => 'required|exists:governments,id',
            'area_id' => 'required|exists:areas,id',
            'full_address' => 'required|string',
            'location_link' => 'nullable|string',
            'phone' => 'required|string|max:11'
        ]);

        $validated['user_id'] = $request->user()->id;

        // check if this is only address or not
        if (!count($request->user()->address)) {
            $validated['is_default'] = true;
        }

        // store data
        $address = UserAddress::create($validated);

        return response()->json([
            'message' => 'address create successfully ',
            'address' => $address
        ], 201);
    }

    public function set_default_address(Request $request, UserAddress $address)
    {
        $addresses = $request->user()->address;

        // set all to false
        foreach ($addresses as $key => $addr) {
            UserAddress::where('id', $addr->id)->update([
                'is_default' => false
            ]);
        }

        $address->update([
            'is_default' => true
        ]);

        return response()->json([
            'message' => 'address set to default successfully',
        ], 200);
    }

    public function destroy(Request $request, UserAddress $address)
    {

        // check if user have more 1 address to can delete address
        $user_addresses = count($request->user()->address);
        $user = $request->user() ;

        if ($user_addresses > 1) {

            // check if this address is default
            if (boolval($address->is_default)) {

                DB::transaction(function () use ($user, $address) {

                    $wasDefault = $address->is_default;

                    $address->delete();

                    if ($wasDefault) {
                        $user->address()
                            ->first()
                            ?->update(['is_default' => true]);
                    }
                });
            } else {
                $address->delete();
            }

            return response()->json([
                'message' => 'address deleted successfully',
            ], 200);
        } else {
            return response()->json([
                'message' => 'you can not delete address you have to let at lest  1 address',
            ], 403);
        }
    }
}
