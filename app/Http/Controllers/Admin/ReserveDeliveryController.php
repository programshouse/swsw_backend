<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Area;
use App\Models\DeliveryUser;
use App\Models\Government;
use App\Models\Level;
use App\Models\Shift;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ReserveDeliveryController extends Controller
{
    public function index()
    {
        $deliveries = DeliveryUser::with(['area', 'shift', 'level', 'vehicle'])
            ->where('is_reserve', 1)
            ->latest()
            ->get();

        return view('admin.delivery.reserve.index', compact('deliveries'));
    }


    public function create()
    {
        $governments = Government::query()
            ->where('is_active', true)
            ->with([
                'areas' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('name_ar');
                }
            ])
            ->orderBy('name_ar')
            ->get();



        $shifts = Shift::query()

            ->orderBy('name_ar')
            ->get();

        $levels = Level::query()
            ->orderBy('name')
            ->get();

        $vehicles = Vehicle::query()
            ->orderBy('name_ar')
            ->get();

        $delivery = null;

        return view('admin.delivery.reserve.create', compact(
            'governments',
            'shifts',
            'levels',
            'vehicles',
            'delivery'
        ));
    }



    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:delivery_users,email',
            'phone' => 'required|string|max:191|unique:delivery_users,phone',
            'birthdate' => 'required|date',
            'password' => 'required|string|min:8|confirmed',

            'government_id' => 'required|exists:governments,id',
            'area_id' => 'required|exists:areas,id',
            'shift_id' => 'required|exists:shifts,id',
            'level_id' => 'nullable|exists:levels,id',

            'type' => 'required|in:company,freelance',
            'has_vehicle' => 'required|boolean',
            'vehicle_id' => 'nullable|exists:vehicles,id',


            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadImage($request);
        }

        $data['password'] = Hash::make($data['password']);
        $data['status'] = 'approved';
        $data['is_reserve'] = 1;
        $data['code'] = $this->generateDeliveryCode();
        $data['shift_code'] = $this->generateUniqueShiftCode();

        DeliveryUser::create($data);

        return redirect()
            ->route('admin.reserve-deliveries.index')
            ->with('success', 'تم إضافة الحساب الاحتياطي بنجاح');
    }

    public function edit(DeliveryUser $reserve_delivery)
    {
        $delivery = $reserve_delivery;

        return view('admin.delivery.reserve.edit', array_merge(
            ['delivery' => $delivery],
            $this->formData()
        ));
    }

    public function update(Request $request, DeliveryUser $reserve_delivery)
    {
        $delivery = $reserve_delivery;

        $data = $request->validate([
            'name' => 'required|string|max:191',
            'email' => [
                'required',
                'email',
                'max:191',
                Rule::unique('delivery_users', 'email')->ignore($delivery->id),
            ],
            'phone' => [
                'required',
                'string',
                'max:191',
                Rule::unique('delivery_users', 'phone')->ignore($delivery->id),
            ],
            'birthdate' => 'required|date',
            'password' => 'nullable|string|min:8|confirmed',

            'government_id' => 'required|exists:governments,id',
            'area_id' => 'required|exists:areas,id',
            'shift_id' => 'required|exists:shifts,id',
            'level_id' => 'nullable|exists:levels,id',

            'type' => 'required|in:company,freelance',
            'has_vehicle' => 'required|boolean',
            'vehicle_id' => 'nullable|exists:vehicles,id',


            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadImage($request);
        }

        $data['status'] = 'approved';
        $data['is_reserve'] = 1;

        $delivery->update($data);

        return redirect()
            ->route('admin.reserve-deliveries.index')
            ->with('success', 'تم تعديل الحساب الاحتياطي بنجاح');
    }

    public function destroy(DeliveryUser $reserve_delivery)
    {
        $reserve_delivery->delete();

        return back()->with('success', 'تم حذف الحساب بنجاح');
    }

    private function formData(): array
    {
        return [
            'governments' => Government::get(),
            'areas' => Area::get(),
            'shifts' => Shift::get(),
            'levels' => Level::get(),
            'vehicles' => Vehicle::get(),
        ];
    }

    private function uploadImage(Request $request): string
    {
        $file = $request->file('image');

        $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $fileName = time() . '_delivery_' . $safeName . '.' . $file->getClientOriginalExtension();

        $path = public_path('uploads/deliveries');

        if (!file_exists($path)) {
            mkdir($path, 0775, true);
        }

        $file->move($path, $fileName);

        return 'uploads/deliveries/' . $fileName;
    }

    private function generateDeliveryCode(): string
    {
        do {
            $code = 'DEL-' . rand(100000, 999999);
        } while (DeliveryUser::where('code', $code)->exists());

        return $code;
    }


    private function generateUniqueShiftCode(): string
    {
        do {
            $shiftCode = (string) random_int(100000, 999999);
        } while (
            DeliveryUser::where('shift_code', $shiftCode)->exists()
        );

        return $shiftCode;
    }
}
