<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Shop\Cart;
use App\Shop\PlaceShopOrder;
use App\Support\Eft;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ShopController extends Controller
{
    /** @var list<string> */
    public const array PROVINCES = [
        'Eastern Cape',
        'Free State',
        'Gauteng',
        'KwaZulu-Natal',
        'Limpopo',
        'Mpumalanga',
        'North West',
        'Northern Cape',
        'Western Cape',
    ];

    public function index(Request $request): View
    {
        $category = $request->query('category');
        $category = is_string($category) && $category !== '' ? $category : null;

        $products = Product::query()
            ->available()
            ->with('media')
            ->when($category !== null, fn ($query) => $query->where('category', $category))
            ->orderBy('name')
            ->get();

        $categories = Product::query()
            ->available()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('shop', [
            'products' => $products,
            'categories' => $categories,
            'category' => $category,
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active && $product->stock_qty > 0 && $product->price_cents > 0, 404);

        return view('shop.show', [
            'product' => $product,
        ]);
    }

    public function add(Request $request, Cart $cart): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $product = Product::query()->available()->find($data['product_id']);
        if ($product === null) {
            return back()->with('cart_error', 'That item is no longer available.');
        }

        $cart->add($product, (int) ($data['qty'] ?? 1));

        return back()->with('cart_open', true);
    }

    public function update(Request $request, Product $product, Cart $cart): RedirectResponse
    {
        $qty = (int) $request->validate([
            'qty' => ['required', 'integer', 'min:0', 'max:99'],
        ])['qty'];

        if ($qty < 1) {
            $cart->remove($product->id);
        } else {
            $cart->set($product, $qty);
        }

        return back()->with('cart_open', true);
    }

    public function remove(Product $product, Cart $cart): RedirectResponse
    {
        $cart->remove($product->id);

        return back()->with('cart_open', true);
    }

    public function checkout(Cart $cart): View|RedirectResponse
    {
        $lines = $cart->lines();
        if ($lines->isEmpty()) {
            return redirect()->route('shop')->with('cart_error', 'Your cart is empty.');
        }

        return view('shop.checkout', [
            'lines' => $lines,
            'cart' => $cart,
            'provinces' => self::PROVINCES,
        ]);
    }

    public function place(Request $request, Cart $cart, PlaceShopOrder $place): RedirectResponse
    {
        if (filled($request->input('company'))) {
            return redirect()->route('shop');
        }

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['required', 'string', 'max:40'],
            'address_line_1' => ['required', 'string', 'max:160'],
            'address_line_2' => ['nullable', 'string', 'max:160'],
            'suburb' => ['required', 'string', 'max:80'],
            'city' => ['required', 'string', 'max:80'],
            'province' => ['required', 'string', Rule::in(self::PROVINCES)],
            'postal_code' => ['required', 'string', 'regex:/^\d{4}$/'],
            'terms' => ['accepted'],
        ]);

        try {
            $order = $place->place($cart, [
                'customer_name' => trim($data['customer_name']),
                'email' => mb_strtolower(trim($data['email'])),
                'phone' => trim($data['phone']),
                'address_line_1' => trim($data['address_line_1']),
                'address_line_2' => filled($data['address_line_2'] ?? null) ? trim((string) $data['address_line_2']) : null,
                'suburb' => trim($data['suburb']),
                'city' => trim($data['city']),
                'province' => $data['province'],
                'postal_code' => $data['postal_code'],
            ]);
        } catch (ValidationException $exception) {
            return redirect()
                ->route('shop.checkout')
                ->withErrors($exception->errors())
                ->with('cart_open', true);
        }

        return redirect()
            ->route('shop.confirmation')
            ->with('shop.confirmation', $order->id);
    }

    public function confirmation(): View|RedirectResponse
    {
        $orderId = session('shop.confirmation');
        $order = is_numeric($orderId)
            ? Order::query()->with('orderItems', 'payment')->find((int) $orderId)
            : null;

        if ($order === null) {
            return redirect()->route('shop');
        }

        return view('shop.confirmation', [
            'order' => $order,
            'eft' => Eft::details(),
        ]);
    }
}
