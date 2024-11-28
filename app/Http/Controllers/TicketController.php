<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Ticket;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();
        if ($currentUser->hasManagerPerms())
        {
            return Ticket::all();
        }
        else
        {
            return $currentUser->tickets;
        }
    }

    public function show(string $ticket)
    {
        $ticket = Ticket::findOrFail($ticket);
        $currentUser = Auth::user();
        if ($currentUser->hasManagerPerms())
        {
            return $ticket;
        }
        return response()->json(['message' => 'unauthorized Access'], 403);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'exists:users,id',
            'ticket_name' => 'string',
            'fault_description' => 'string',

            'customer_name' => 'string',
            'phone_number' => 'string',
        ]);

        if ($validator->fails())
        {
            return response()->json(['message' => 'Invalid Request'], 401);
        }
        
        $currentUser = Auth::user();
        if (!$currentUser->hasManagerPerms())
        {
            return response()->json(['message' => 'unauthorized Request'], 403);
        }

        $validated = $validator->safe()->only(['customer_name', 'phone_number']);
        $customer = Customer::firstOrCreate($validated);

        $validated = $validator->safe()->only(['employee_id', 'ticket_name', 'fault_description']);
        $ticket = new Ticket($validated);

        $customer->tickets()->save($ticket);
        return $ticket;
    }

    public function update(Request $request, string $ticket)
    {
        $ticket = Ticket::findOrFail($ticket);

        $validator = Validator::make($request->all(), [
            'employee_id' => 'exists:users,id',
            'ticket_name' => 'string',
            'fault_description' => 'string',
            'repair_status' => 'string|in:PENDING,FIXED',
            'payment_status' => 'string|in:PENDING,PAID',
            'cost' => 'decimal:0,2|min:0|required_if:payment_status,PAID',
            'profit' => 'decimal:0,2|min:0|required_if:payment_status,PAID',
            'commission_rate' => 'decimal:0,2|min:0|max:1|required_if:payment_status,PAID',

            'customer_name' => 'string',
            'phone_number' => 'string',
        ]);

        if ($validator->fails())
        {
            return response()->json(['message' => 'Invalid Request'], 401);
        }

        $currentUser = Auth::user();
        if ($currentUser->hasManagerPerms())
        {
            $validated = $validator->safe()->only(['employee_id', 'ticket_name', 'fault_description', 'repair_status', 'payment_status', 'cost', 'profit', 'commission_rate']);
            $ticket->update($validated);
            
            $validated = $validator->safe()->only(['customer_name', 'phone_number']);
            $ticket->customer()->update($validated);
        }
        elseif ($currentUser->id == $ticket->employee_id)
        {
            $validated = $validator->safe()->only(['fault_description', 'cost']);
            $ticket->update($validated);
        }
        else
        {
            return response()->json(['message' => 'Unauthorized Request'], 403);
        }

        return $ticket;
    }

    public function destroy(string $ticketId)
    {
        $currentUser = Auth::user();
        if ($currentUser->hasManagerPerms())
        {
            $ticket = Ticket::findOrFail($ticketId);
            $ticket->delete();

            return response()->json(['message' => 'Ticket Deleted Successfully'], 200);
        }
        return response()->json(['message' => 'Unauthorized Request'], 403);
    }
}
