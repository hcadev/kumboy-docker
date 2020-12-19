<?php
namespace App\Http\Controllers\Profile\Store;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSpecification;
use App\Models\Store;
use App\Traits\Validation\HasProductValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ProductController extends ProfileController
{
    use HasProductValidation;

    public function search($store_id, Request $request)
    {
        $category = explode('|', $request->get('category', []));
        $sort = explode('|', $request->get('sort', []));

        return redirect()
            ->route('store.products', [
                'id' => $store_id,
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

    public function viewStoreProducts(
        $store_id,
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
        $products = Product::query()
            ->where('store_id', $store_id);

        if (empty($keyword) === false) {
            $products->whereRaw('MATCH (name) AGAINST (? IN BOOLEAN MODE)', [$keyword.'&']);
        }

        if ($main_category !== 'all') {
            $products->where('main_category', $main_category);
        }

        if ($sub_category !== 'all') {
            $products->where('sub_category', $sub_category);
        }

        $products->whereBetween('price', [$price_from ?? 0, $price_to ?? 1000000]);

        $offset = ($current_page - 1) * $items_per_page;
        $total_count = $products->count();

        $products
            ->skip($offset)
            ->take($items_per_page)
            ->orderBy(
                in_array($sort_by, ['name', 'price', 'sold']) ? $sort_by : 'sold',
                in_array($sort_dir, ['asc', 'desc']) ? $sort_dir : 'desc'
            );

        $product_categories = Product::query()
            ->select(['main_category', 'sub_category'])
            ->where('store_id', $store_id)
            ->groupBy('main_category', 'sub_category')
            ->get();

        return view('stores.profile.products.index')
            ->with('products', $products->get())
            ->with('product_categories', $product_categories)
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
                ->with('url', route('store.products', $store_id))
            );
    }

    public function showAddProductForm($store_id)
    {
        $store = Store::query()->find($store_id);

        $this->authorize('addProduct', $store);

        return view('stores.profile.products.form')
            ->with('form_title', 'Add Product')
            ->with('categories', config('system.product_categories'));
    }

    public function addProduct(Request $request, $store_id)
    {
        $store = Store::query()->find($store_id);

        $this->authorize('addProduct', $store);

        $validated_data = $request->validate($this->getProductRules());

        try {
            $uploaded_images = [];

            if ($request->files->count() === 0) {
                return back()
                    ->withErrors(['images' => 'Images are required.'])
                    ->withInput($request->all());
            } else {
                $this->beginTransaction();

                // create product record
                $product_category = explode('|', $validated_data['category']);
                $product = Product::query()
                    ->create([
                        'store_id' => $store_id,
                        'name' => $validated_data['name'],
                        'qty' => $validated_data['qty'],
                        'price' => $validated_data['price'],
                        'main_category' => $product_category[0],
                        'sub_category' => $product_category[1] === 'all' ? null : $product_category[1],
                    ]);

                // insert product specifications
                $specifications = explode('|', $validated_data['specifications']);
                foreach ($specifications AS $spec) {
                    list($name, $value) = explode(':', $spec);

                    ProductSpecification::query()
                        ->create([
                            'product_id' => $product->id,
                            'name' => trim($name),
                            'value' => trim($value),
                        ]);
                }

                // validate images
                $files = $request->file('images');
                for ($i = 0; $i < count($files); $i++) {
                    // only allow png and jpeg
                    $ext = substr($files[$i]->getMimeType(), strpos($files[$i]->getMimeType(), '/') + 1);

                    if (in_array($ext, ['jpeg', 'png']) === false) {
                        return back()
                            ->withErrors(['images' => 'Some images have invalid format.'])
                            ->withInput($request->all());
                    }

                    // file size must not exceed 500kb
                    if ($files[$i]->getSize() / 1024 > 500) {
                        return back()
                            ->withErrors(['images' => 'Some files are too large.'])
                            ->withInput($request->all());
                    }

                    $image = Image::make($files[$i]);

                    // resize image to 512x512
                    if ($image->width() === $image->height()) {
                        // square
                        $image->resize(500, 500);
                        $image->resizeCanvas(512, 512, 'center', false, '#ffffff');
                    } elseif ($image->width() > $image->height()) {
                        // horizontal, pad left and right
                        $image->resize(500, null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                        $image->resizeCanvas(512, 512, 'center', false, '#ffffff');
                    } elseif ($image->width() < $image->height()) {
                        // vertical, pad top and bottom
                        $image->resize(null, 500, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                        $image->resizeCanvas(512, 512, 'center', false, '#ffffff');
                    }

                    $filename = $store_id.$product->id.substr(strtotime('now'), -6).$i.'.'.$ext;
                    $uploaded_images[] = 'products/images/thumbnail/'.$filename;

                    // upload original
                    Storage::put('products/images/original/'.$filename, (string) $image->encode());

                    //upload preview
                    $image->resize(150, 150);
                    Storage::put('products/images/preview/'.$filename, (string) $image->encode());

                    //upload thumbnail
                    $image->resize(50, 50);
                    Storage::put('products/images/thumbnail/'.$filename, (string) $image->encode());

                    ProductImage::query()
                        ->create([
                            'product_id' => $product->id,
                            'filename' => $filename,
                        ]);

                    if ($i === 0) {
                        $product->update(['preview' => $filename]);
                    }
                }

                $this->commit();

                return redirect()
                    ->route('store.products', $store_id)
                    ->with('message_type', 'success')
                    ->with('message_content', $product->name.' has been added.');
            }
        } catch (\Exception $e) {
            $this->rollback();
            logger($e);

            // delete uploaded images
            foreach ($uploaded_images AS $image) {
                Storage::delete($image);
            }

            return back()
                ->with('message_type', 'danger')
                ->with('message_content', 'Server error.')
                ->withInput($request->all());
        }
    }

    public function showEditProductForm($store_id, $product_id)
    {
        $store = Store::query()->find($store_id);

        $product = Product::query()
            ->where('id', $product_id)
            ->where('store_id', $store_id)
            ->with(['specifications' => function ($query) {
                $query->orderBy('name', 'asc');
            }])
            ->with('images')
            ->first();

        if ($product === null) {
            abort(404);
        }

        $this->authorize('editProduct', $store);

        return view('stores.profile.products.form')
            ->with('form_title', 'Edit Product')
            ->with('form_data', $product)
            ->with('categories', config('system.product_categories'));
    }

    public function updateProduct(Request $request, $store_id, $product_id)
    {
        $store = Store::query()->find($store_id);
        $product = Product::query()->find($product_id);

        if ($product === null) {
            abort(404);
        }

        $this->authorize('editProduct', $store);

        $validated_data = $request->validate($this->getProductRules());

        try {
            $uploaded_images = [];

            $this->beginTransaction();

            // update product
            $product_category = explode('|', $validated_data['category']);
            $product->update([
                'name' => $validated_data['name'],
                'qty' => $validated_data['qty'],
                'price' => $validated_data['price'],
                'main_category' => $product_category[0],
                'sub_category' => $product_category[1] === 'all' ? null : $product_category[1],
            ]);

            // delete current specifications
            ProductSpecification::query()
                ->where('product_id', $product_id)
                ->delete();

            // insert product specifications
            $specifications = explode('|', $validated_data['specifications']);
            foreach ($specifications AS $spec) {
                list($name, $value) = explode(':', $spec);

                ProductSpecification::query()
                    ->create([
                        'product_id' => $product->id,
                        'name' => trim($name),
                        'value' => trim($value),
                    ]);
            }

            if ($request->files->count() > 0) {
                // validate images
                $files = $request->file('images');
                for ($i = 0; $i < count($files); $i++) {
                    // only allow png and jpeg
                    $ext = substr($files[$i]->getMimeType(), strpos($files[$i]->getMimeType(), '/') + 1);

                    if (in_array($ext, ['jpeg', 'png']) === false) {
                        return back()
                            ->withErrors(['images' => 'Some images have invalid format.'])
                            ->withInput($request->all());
                    }

                    // file size must not exceed 500kb
                    if ($files[$i]->getSize() / 1024 > 500) {
                        return back()
                            ->withErrors(['images' => 'Some files are too large.'])
                            ->withInput($request->all());
                    }

                    $image = Image::make($files[$i]);

                    // resize image to 512x512
                    if ($image->width() === $image->height()) {
                        // square
                        $image->resize(500, 500);
                        $image->resizeCanvas(512, 512, 'center', false, '#ffffff');
                    } elseif ($image->width() > $image->height()) {
                        // horizontal, pad left and right
                        $image->resize(500, null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                        $image->resizeCanvas(512, 512, 'center', false, '#ffffff');
                    } elseif ($image->width() < $image->height()) {
                        // vertical, pad top and bottom
                        $image->resize(null, 500, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                        $image->resizeCanvas(512, 512, 'center', false, '#ffffff');
                    }

                    $filename = $store_id.$product->id.substr(strtotime('now'), -6).$i.'.'.$ext;
                    $uploaded_images[] = 'products/images/thumbnail/'.$filename;

                    // upload original
                    Storage::put('products/images/original/'.$filename, (string) $image->encode());

                    //upload preview
                    $image->resize(150, 150);
                    Storage::put('products/images/preview/'.$filename, (string) $image->encode());

                    //upload thumbnail
                    $image->resize(50, 50);
                    Storage::put('products/images/thumbnail/'.$filename, (string) $image->encode());

                    ProductImage::query()
                        ->create([
                            'product_id' => $product->id,
                            'filename' => $filename,
                        ]);

                    if ($i === 0) {
                        $product->update(['preview' => $filename]);
                    }
                }
            }

            $this->commit();

            // process deleted images
            if (empty($request->get('removed', [])) === false) {
                foreach ($request->get('removed') AS $id => $value) {
                    $product_image = ProductImage::query()->find($id);
                    $product_image->delete();

                    if ($product->preview === $product_image->filename) {
                        $new_preview = ProductImage::query()
                            ->where('product_id', $product_id)
                            ->inRandomOrder()
                            ->first();

                        $product->update(['preview' => $new_preview->filename]);
                    }

                    Storage::delete('products/images/original/'.$product_image->filename);
                    Storage::delete('products/images/preview/'.$product_image->filename);
                    Storage::delete('products/images/thumbnail/'.$product_image->filename);
                }
            }

            return back()
                ->with('message_type', 'success')
                ->with('message_content', 'Product has been updated.');
        } catch (\Exception $e) {
            $this->rollback();
            logger($e);

            // delete uploaded images
            foreach ($uploaded_images AS $image) {
                Storage::delete($image);
            }

            return back()
                ->with('message_type', 'danger')
                ->with('message_content', 'Server error.')
                ->withInput($request->all());
        }
    }
}
