<?php
namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends DatabaseController
{
    public function search(Request $request)
    {
        $category = explode('|', $request->get('category', []));
        $sort = explode('|', $request->get('sort', []));

        return redirect()
            ->route('product.view-all', [
                'current_page' => 1,
                'items_per_page' => 15,
                'price_from' => $request->get('price_from', 0),
                'price_to' => $request->get('price_to', 1000000),
                'main_category' => $category[0] ?? 'all',
                'sub_category' => $category[1] ?? 'all',
                'sort_by' => $sort[0] ?? 'sold',
                'sort_dir' => $sort[1] ?? 'desc',
                'keyword' => $request->get('keyword'),
            ]);
    }

    public function viewAll(
        Request $request,
        $current_page = 1,
        $items_per_page = 12,
        $price_from = 0,
        $price_to = 1000000,
        $main_category = 'all',
        $sub_category = 'all',
        $sort_by = 'sold',
        $sort_dir = 'desc',
        $keyword = null
    ) {
        if (Cache::has($request->url())) {
            dd(Cache::get($request->url()));
        } else {
            $products = Product::query();

            if (empty($keyword) === false) {
                $products->whereRaw('MATCH (name) AGAINST (? IN BOOLEAN MODE)', [$keyword.'&']);
            }

            if (empty($main_category) === false AND $main_category !== 'all') {
                $products->where('main_category', $main_category);
            }

            if (empty($sub_category) === false AND $sub_category !== 'all') {
                $products->where('sub_category', $sub_category);
            }

            $products->whereBetween('price', [$price_from ?? 0, $price_to ?? 1000000]);

            $offset = ($current_page - 1) * $items_per_page;
            $total_count = $products->count();

            $products->skip($offset)
                ->take($items_per_page)
                ->orderBy(
                    in_array($sort_by, ['name', 'price', 'sold']) ? $sort_by : 'sold',
                    in_array($sort_dir, ['asc', 'desc']) ? $sort_dir : 'desc'
                );

            Cache::tags('shop')->put($request->url(), $products->get(), 3600);
        }

        return view('products.index')
            ->with('products', $products->get())
            ->with('product_categories', config('system.product_categories'))
            ->with('filters', [
                'keyword' => $keyword,
                'main_category' => $main_category,
                'sub_category' => $sub_category,
                'price_from' => $price_from,
                'price_to' => $price_to,
                'sort_by' => $sort_by,
                'sort_dir' => $sort_dir,
            ])
            ->with('pagination', view('shared.pagination')
                ->with('item_start', $offset + 1)
                ->with('item_end', $products->count() + $offset)
                ->with('total_count', $total_count)
                ->with('current_page', $current_page)
                ->with('total_pages', ceil($total_count / $items_per_page))
                ->with('items_per_page', $items_per_page)
                ->with('keyword', $keyword)
                ->with('url', route('product.view-all'))
            );
    }
}
