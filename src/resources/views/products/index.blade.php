@extends('layouts.app')
@section('page-title', 'Products')

@section('content')
    <div class="container">
        <div class="row mt-2 mt-md-5">
            <div class="col">
                <h4 class="border-bottom my-0 p-2">Products</h4>
                <div class="row mt-3">
                    <div class="col-12 col-md-3">
                        <form action="{{ route('product.search') }}" method="POST">
                            @csrf

                            <div class="mb-2">
                                <input type="search" name="keyword" class="form-control form-control-sm" value="{{ $filters['keyword'] ?? '' }}" placeholder="Search keyword...">
                            </div>

                            <div class="mb-2">
                                <label>Categories</label>
                                <select name="category" class="form-select form-select-sm">
                                    @foreach ($product_categories AS $key => $value)
                                        @php $category = $key.'|all'; @endphp
                                        <option value="{{ $category }}"
                                                {{ (isset($filters['main_category'], $filters['sub_category']) AND $filters['main_category'] === $key AND $filters['sub_category'] === 'all') ? 'selected' : '' }}
                                        >{{ ucwords($key).' - All' }}</option>

                                        @if (empty($value) === false AND is_array($value))
                                            @foreach ($value AS $sub_value)
                                                @php $category = $key.'|'.$sub_value; @endphp
                                                <option value="{{ $category }}"
                                                        {{ (isset($filters['main_category'], $filters['sub_category']) AND $filters['main_category'] === $key AND $filters['sub_category'] === $sub_value) ? 'selected' : '' }}
                                                >{{ ucwords($key.' - '.$sub_value) }}</option>
                                            @endforeach
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-2">
                                <label>Price</label>
                                <div class="d-flex align-items-center">
                                    <input type="number" name="price_from" class="form-control form-control-sm" value="{{ $filters['price_from'] ?? 0 }}" min="0" max="1000000" step="0.01">
                                    <span class="mx-2">to</span>
                                    <input type="number" name="price_to" class="form-control form-control-sm" value="{{ $filters['price_to'] ?? 1000000 }}" min="0" max="1000000" step="0.01">
                                </div>
                            </div>

                            <div class="mb-2">
                                <label>Sort By</label>
                                <select name="sort" class="form-select form-select-sm">
                                    <option value="best sellers" {{ (isset($filters['sort_by']) AND $filters['sort_by'] === 'best sellers') ? 'selected' : '' }}>Best Sellers</option>
                                    <option value="name|asc" {{ (isset($filters['sort_by'], $filters['sort_dir']) AND $filters['sort_by'] === 'name' AND $filters['sort_dir'] === 'asc') ? 'selected' : '' }}>Name A-Z</option>
                                    <option value="name|desc" {{ (isset($filters['sort_by'], $filters['sort_dir']) AND $filters['sort_by'] === 'name' AND $filters['sort_dir'] === 'desc') ? 'selected' : '' }}>Name Z-A</option>
                                    <option value="price|asc" {{ (isset($filters['sort_by'], $filters['sort_dir']) AND $filters['sort_by'] === 'price' AND $filters['sort_dir'] === 'asc') ? 'selected' : '' }}>Highest Price</option>
                                    <option value="price|desc" {{ (isset($filters['sort_by'], $filters['sort_dir']) AND $filters['sort_by'] === 'price' AND $filters['sort_dir'] === 'desc') ? 'selected' : '' }}>Lowest Price</option>
                                </select>
                            </div>

                            <div class="d-grid d-block">
                                <button type="submit" class="btn btn-primary btn-sm">Apply Filter</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-12 col-md-9 mt-3 mt-md-0">
                        @if ($products->isEmpty())
                            <div class="alert alert-danger">No records found.</div>
                        @else
                            <div class="row row-cols-2 row-cols-md-6 gx-5 gx-md-1 gy-3">
                                @foreach ($products AS $product)
                                    <div class="col">
                                        <a href="{{ route('product.info', $product->id) }}" class="card-link-wrapper">
                                            <div class="card product-listing">
                                                <img src="{{ asset('storage/products/images/preview/'.(file_exists('storage/products/images/preview/'.$product->preview) ? $product->preview : 'no-image.jpg')) }}" class="card-img-top">
                                                <div class="card-body p-2">
                                                    <p class="mb-1 small ellipsis">{{ $product->name }}</p>
                                                    <p class="mb-1 text-primary">&#8369;{{ number_format($product->price, 2, '.', ',') }}</p>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                            @php echo $pagination @endphp
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection