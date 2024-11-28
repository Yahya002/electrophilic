<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        if ($currentUser->hasManagerPerms())
        {
            $query = Commission::query();

            $validator = Validator::make($request->all,[
                
                'filters.status' => 'in:pending,paid',
                'filters.amount_range.min' => 'decimal:0,2|min:0',
                'filters.amount_range.max' => 'decimal:0,2|min:0',
                
                'filters.created_at.from' => 'date',
                'filters.created_at.to' => 'date',
                
                'filters.updated_at.from' => 'date',
                'filters.updated_at.to' => 'date',
                
                'sort.field' => 'required_with:sort,sort.order|in:amount,created_at,updated_at',
                'sort.order' => 'required_with:sort,sort.field|in:desc,asc',

                // 'pagination.page'
            ]);

            $filters = $validator->safe()->except(['sort']);
            $filters = $filters['filters'];
            $sorter = $validator->safe()->only(['sort']);

            // filters

            if (isset($filters['status']))
            {
                $query->where('status', $filters['status']);
            }    

            if (isset($filters['amount_range']))
            {
                if (isset($filters['amount_range']['min']) && isset($filters['amount_range']['max'])) {
                    $query->whereBetween('amount', [$filters['amount_range']['min'], $filters['amount_range']['max']]);
                } else if (isset($filters['amount_range']['min'])) {
                    $query->where('amount', '<=', $filters['amount_range']['min']);
                } else if (isset($filters['amount_range']['max'])) {
                    $query->where('amount', '>=', $filters['amount_range']['min']);
                }
            }

            if (isset($filters['created_at']))
            {
                if (isset($filters['created_at']['from']) && isset($filters['created_at']['to'])) {
                    $query->whereBetween('amount', [$filters['created_at']['from'], $filters['created_at']['to']]);
                } else if (isset($filters['created_at']['from'])) {
                    $query->where('amount', '<=', $filters['created_at']['from']);
                } else if (isset($filters['created_at']['to'])) {
                    $query->where('amount', '>=', $filters['created_at']['from']);
                }
            }

            if (isset($filters['updated_at']))
            {
                if (isset($filters['updated_at']['from']) && isset($filters['updated_at']['to'])) {
                    $query->whereBetween('amount', [$filters['updated_at']['from'], $filters['updated_at']['to']]);
                } else if (isset($filters['updated_at']['from'])) {
                    $query->where('amount', '<=', $filters['updated_at']['from']);
                } else if (isset($filters['updated_at']['to'])) {
                    $query->where('amount', '>=', $filters['updated_at']['from']);
                }
            }

            // sorting
            if (isset($sorter['sort'])) {
                $query->orderBy($sorter['sort']['field'], $sorter['sort']['order']);
            } else {
                $query->orderBy('created_at', 'desc');
            }
        }
        else
        {
            return $currentUser->commissions;
        }
    }

    public function show(string $commission)
    {
        $commission = Commission::findOrFail($commission);
        $currentUser = Auth::user();
        if ($currentUser->hasManagerPerms() || $currentUser->id == $commission->employee_id)
        {
            return $commission;
        }

        return response()->json(['message' => 'unauthorized Access'], 403);
    }

    // public function store(Request $request)
    // {
    //     $currentUser = Auth::user();
    //     if (!$currentUser->hasManagerPerms())
    //     {
    //         return response()->json(['message' => 'unauthorized Request'], 403);
    //     }
        
    //     $validator = Validator::make($request->all(), [
    //         'ticket_id' => 'exists:tickets,id',
    //     ]);

    //     $validated = $validator->safe()->only(['ticket_id']);

    //     $commission = new commission([
    //         'ticket_id' => $validated['ticket_id'],
    //     ]);

    //     $commission->save();
    //     return $commission;
    // }

    // public function update(Request $request, string $ticket)
    // {
    //     $commission = Commission::findOrFail($ticket);

    //     $validator = Validator::make($request->all(), [
    //         'employee_id' => 'exists:employees',
    //         'fault_description' => 'string',
    //         'status' => 'string', //validate enum
    //         'cost' => 'decimal|min:0',
    //         'profit' => 'decimal|min:0',
    //         'commission' => 'decimal|min:0',

    //         'name' => 'string',
    //         'phone_number' => 'string',
    //     ]);

    //     $currentUser = Auth::user();
    //     if ($currentUser->hasManagerPerms())
    //     {
    //         $validated = $validator->safe()->only(['employee_id', 'fault_description', 'status', 'cost', 'profit', 'commission']); 
    //         $commission->update($validated);
    //         return $commission;
    //     }

    //     return response()->json(['message' => 'Unauthorized Request'], 403);
    // }

    public function destroy(string $commissionId)
    {
        $currentUser = Auth::user();
        if ($currentUser->hasOwnerPerms())
        {
            $commission = Commission::findOrFail($commissionId);
            $commission->delete();
            return response()->json(['message' => 'Commission Deleted Successfully'], 200);
        }

        return response()->json(['message' => 'Unauthorized Request'], 403);
    }
}
