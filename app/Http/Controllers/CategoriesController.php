<?php


namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Category;
use Gloudemans\Shoppingcart\CartItem;
use Gloudemans\Shoppingcart\Facades\Cart;

class CategoriesController extends Controller
{
    public function index()
    {

        $categories = Category::all();

        return view('categories.index', compact('categories' ));
    }

    public function show(Category $category)
    {
        $gallery = collect($category->images()->get()->map(fn($image) => $image->url));
        $gallery->prepend($category->thumbnailUrl);
        $rowId = $this->getProductFromCart($category)?->rowId;
        $isInCart = !!$rowId;

        return view('$categories.show', compact('category', 'gallery', 'isInCart', 'rowId'));
    }

    protected function getProductFromCart(Category $category): CartItem|null
    {
        return Cart::instance('cart')
            ->content()
            ->where('id', '=', $category->id)
            ?->first();
    }

}

