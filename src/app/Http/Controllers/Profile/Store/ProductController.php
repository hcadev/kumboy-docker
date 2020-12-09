<?php
namespace App\Http\Controllers\Profile\Store;

class ProductController extends ProfileController
{
    public function viewStoreProducts(
        $storeUuid,
        $category = '*',
        $sortBy = 'name',
        $sortDir = 'asc',
        $currentPage = 1,
        $itemsPerPage = 15,
        $keyword = null)
    {
        return view('stores.profile.products.index');
    }
}
