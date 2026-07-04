<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use App\Models\CourseBasicInfo;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Models\LeadPayment;

class PaymentController extends Controller
{
    protected $razorpay;

    public function __construct()
    {
        $this->razorpay = new Api(env('RAZORPAY_KEY_ID'), env('RAZORPAY_KEY_SECRET'));
    }

    public function createPayment(Request $request)
    {

        // Get necessary details from the request
        $amount = 1;
        $currency = 'INR'; // Default to INR if not provided
        $description = Auth::user()->id.'-'.Auth::user()->name.'-'.CourseBasicInfo::find($request->id)->course_name;

        // Create a Razorpay order
        /*$order = $this->razorpay->order->create([
            'amount' => $amount * 100, // Razorpay requires amount in paisa
            'currency' => $currency,
            'receipt' => uniqid(),
        ]);*/
        // Return the Razorpay order ID to the view for further processing
        return view('payment.create', [
            //'orderId' => $order->id,
            'amount' => $amount,
            'currency' => $currency,
            'description'=> $description,
        ]);
    }

    public function paymentCallback(Request $request)
    {
        // Handle the payment callback here (e.g., verify payment, update database, etc.)
        // You can find the details in the Razorpay documentation
    }

    public function paymentupdate($id)
    {
                $payment = LeadPayment::findOrFail($id);
                $payment->payment_status = 1;
                // Save the updated record
                $payment->save();
			 return redirect()->route('pendingpayment')->with('success', 'Amount Paid Successfully');

    }
    
    public function store(Request $request): JsonResponse
{
    DB::beginTransaction();

    try {
        $paymentResponse = $request->input('response', []);

        if (!empty($paymentResponse)) {
            if (count($paymentResponse) > 0 && empty($paymentResponse['razorpay_payment_id'])) {
                Session::put('error', 'No Payment ID Found');
                DB::rollBack(); // rollback before returning
                return response()->json(['success' => false, 'message' => 'No Payment ID Found']);
            }

            $payment = $this->razorpay->payment->fetch($paymentResponse['razorpay_payment_id']);
            $response = $payment->capture(['amount' => $payment['amount']]);

            Payment::create([
                'r_payment_id' => $response->id,
                'user_id' => $request->input('user_id'),
                'course_id' => $request->input('course_id'),
                'method' => $response->method,
                'currency' => $response->currency,
                'email' => $response->email,
                'phone' => $response->contact,
                'amount' => $response->amount / 100,
                'status' => 'success',
                'json_response' => json_encode((array) $response)
            ]);

            Session::put('success', 'Payment Successful, Soon admin will assign you the course.');
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Payment successfully recorded']);
        }

        // 👇 Default fallback when $paymentResponse is empty
        DB::rollBack();
        return response()->json(['success' => false, 'message' => 'Empty payment response'], 400);

    } catch (\Throwable $th) {
        DB::rollBack();
        Log::error('PAYMENT_STORE_ERROR: '.$th->getMessage());
        Session::put('error', $th->getMessage());

        return response()->json(['success' => false, 'error' => 'Internal Server Error'], 500);
    }
}


    public function storeOLD(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $paymentResponse = $request->input('response', []);
			if(!empty($paymentResponse))
			{
            if (count($paymentResponse) > 0 && empty($paymentResponse['razorpay_payment_id'])) {
                Session::put('error', 'No Payment ID Found');

                return redirect()->back();
            }

            $payment = $this->razorpay->payment->fetch($paymentResponse['razorpay_payment_id']);
            $response = $payment->capture(['amount' => $payment['amount']]);

            Payment::create([
                'r_payment_id' => $response->id,
                'user_id' => $request->input('user_id'),
                'course_id' => $request->input('course_id'),
                'method' => $response->method,
                'currency' => $response->currency,
                'email' => $response->email,
                'phone' => $response->contact,
                'amount' => $response->amount / 100,
                'status' => 'success',
                'json_response' => json_encode((array) $response)
            ]);

            Session::put('success', 'Payment Successful, Soon admin will assign you the course.');
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Payment successfully recorded']);
			}
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('PAYMENT_STORE_ERROR'.$th->getMessage());
            Session::put('error', $th->getMessage());

            return response()->json(['success' => false, 'error' => 'Internal Server Error'], 500);
        }
    }

    public function failure(Request $request): JsonResponse {
        DB::beginTransaction();

        try {
            $responseData = $request->input('response', []);
            $errorData = $responseData['error'] ?? [];

            Payment::create([
                'r_payment_id' => $errorData['metadata']['payment_id'] ?? null,
                'user_id' => $request->input('user_id'),
                'course_id' => $request->input('course_id'),
                'method' => $errorData['source'] ?? null,
                'currency' => 'INR',
                'email' => $request->input('email'), //email id for the the user
                'phone' => '', // mobile number for the user,
                'amount' => $request->input('amount'), // amount for the payment process
                'status' => 'failed',
                'json_response' => json_encode($responseData)
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Payment failure recorded']);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('PAYMENT_FAILURE_ERROR: '.$th->getMessage());
            return response()->json(['success' => false, 'error' => 'Internal Server Error'], 500);
        }
    }
}
