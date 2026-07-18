<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::query()
            ->withCount([
                'kitchens',
            ])
            ->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $companies = $query
            ->paginate(15)
            ->withQueryString();

        $statistics = [
            'total' => Company::count(),
            'active' => Company::where('is_active', true)->count(),
            'inactive' => Company::where('is_active', false)->count(),
            'kitchens' => Company::withCount('kitchens')->get()->sum('kitchens_count'),
        ];

        return view('admin.companies.index', compact(
            'companies',
            'statistics'
        ));
    }

    public function create()
    {
        return view('admin.companies.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateCompany($request);

       

            $data['is_active'] = $request->boolean('is_active');

            Company::create($data);
       

        return redirect()
            ->route('admin.companies.index')
            ->with('success', 'تم إضافة الشركة بنجاح');
    }

    public function show(Company $company)
    {
        $company->load([
            'kitchens' => function ($query) {
                $query->latest();
            },
        ]);

        return view('admin.companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('admin.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $data = $this->validateCompany($request, $company);

       
            $data['is_active'] = $request->boolean('is_active');

            $company->update($data);
        

        return redirect()
            ->route('admin.companies.index')
            ->with('success', 'تم تحديث بيانات الشركة بنجاح');
    }

    public function destroy(Company $company)
    {
        if ($company->users()->exists()) {
            return redirect()
                ->route('admin.companies.index')
                ->with('error', 'لا يمكن حذف الشركة لأنها مرتبطة بمستخدمين أو مطابخ');
        }

        DB::transaction(function () use ($company) {
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }

            $company->delete();
        });

        return redirect()
            ->route('admin.companies.index')
            ->with('success', 'تم حذف الشركة بنجاح');
    }

    public function toggleStatus(Company $company)
    {
        $company->update([
            'is_active' => !$company->is_active,
        ]);

        return redirect()
            ->back()
            ->with(
                'success',
                $company->is_active
                    ? 'تم تفعيل الشركة بنجاح'
                    : 'تم إيقاف الشركة بنجاح'
            );
    }

    private function validateCompany(
        Request $request,
        ?Company $company = null
    ): array {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('companies', 'name')->ignore($company?->id),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('companies', 'phone')->ignore($company?->id),
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('companies', 'email')->ignore($company?->id),
            ],

          

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ], [
            'name.required' => 'اسم الشركة مطلوب',
            'name.unique' => 'اسم الشركة مستخدم من قبل',
            'phone.unique' => 'رقم الهاتف مستخدم من قبل',
            'email.email' => 'البريد الإلكتروني غير صحيح',
           
          
        ]);
    }





    public function companies(): JsonResponse
{
    $companies = Company::where('is_active', true)
        ->orderBy('name')
        ->get([
            'id',
            'name',
           
        ]);

    $companies->transform(function ($company) {
        return [
            'id' => $company->id,
            'name' => $company->name,
            
        ];
    });

    return response()->json([
        'status' => true,
        'message' => 'Companies retrieved successfully',
        'data' => $companies,
    ]);
}

 }
