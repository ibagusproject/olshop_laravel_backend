<?php

namespace App\Http\Controllers\API;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $status = $request->input('status');
        $limit = $request->input('limit', 6);

        if ($id) {
            $transaction = Transaction::with(['items.product'])->find($id);
            if ($transaction) {
                return ApiResponse::success($transaction, 'Data Transaction Berhasil Diambil');
            } else {
                return ApiResponse::error(null, 'Data Transaction Tidak Ada', 404);
            }
        }

        $transaction = Transaction::with(['items.product'])->where('users_id', Auth::user()->id);

        if ($status) {
            $transaction->where('status', 'like', "%$status%");
        }

        return ApiResponse::success($transaction->paginate($limit), 'Data Transaction Berhasil Diambil');
    }

    public function checkout(Request $request)
    {
        // validasi input dari user
        $validator = Validator::make($request->all(), [
            'items' => ['required', 'array'],
            'items.*.id' => ['exists:products,id'],
            'total_price' => ['required'],
            'shipping_price' => ['required'],
            'status' => ['required', 'in:PENDING,SUCCESS,CANCELED,FAILED,SHIPPING,SHIPPED'],
        ]);

        // jika validasi gagal
        if ($validator->fails()) {
            return ApiResponse::error($validator->getMessageBag(), 'Checkout Failed', 500);
        }

        // jika validasi berhasil
        // simpan data checkout baru
        $trx = Transaction::create([
            'user_id' => Auth::user()->id,
            'address' => $request->address,
            'total_price' => $request->total_price,
            'shopping_price' => $request->shopping_price,
            'status' => $request->status,
        ]);

        foreach ($request->items as $product) {
            TransactionItem::create([
                'user_id' => Auth::user()->id,
                'products_id' => $product['id'],
                'transactions_id' => $trx->id,
                'quantity' => $product['quantity'],
            ]);
        }

        return ApiResponse::success($trx->load('items.product'), 'Transaction Berhasil');
    }
}
