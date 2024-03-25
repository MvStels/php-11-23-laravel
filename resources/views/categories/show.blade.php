
@extends('layouts.app')

@section('content')
    <div class="album py-5 bg-body-tertiary">
     <div class="container">
                <div class="row row-cols-1 row-cols-sm-3 row-cols-md-4 g-4">
                    @each('products.parts.card', $category->products, 'product')
                </div>
            </div>

@endsection
