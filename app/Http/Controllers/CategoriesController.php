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
//        $gallery = collect($category-->get()->map(fn($image) => $image->url));
//        $gallery->prepend($category->perenet_id);
//        $rowId = $this->getProductFromCart($category)?->rowId;
//        $isInCart = !!$rowId;

        return view('categories.show', compact('category'));
    }



}

