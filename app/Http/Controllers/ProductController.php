<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;
use App\Models\Category_product;
use App\Models\Supplier;

//import return type redirectResponse
use Illuminate\Http\RedirectResponse;

//import Facades Storage
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
    * index
    *
    * @return void
    */
    public function index() : View
    {
        //get all products
        $product = new Product;
        $products = $product->get_product()->latest()->paginate(10);

        //render view with products
        return view('products.index', compact('products'));
    }


    // ------------------------------------------------------------------
    // CRUD
    // ------------------------------------------------------------------

    /**
    * create
    *
    * @return View
    */
    public function create(): View
    {
        // Mengambil semua kategori
        $categoryModel = new Category_product;
        $data['categories'] = $categoryModel->get_category_product()->get(); 

        // Mengambil semua supplier
        $supplierModel = new Supplier;
        $data['suppliers'] = $supplierModel->get_supplier()->get();

        return view('products.create', compact('data')); 
    }


    /**
    * store
    * digunakan untuk insert data ke dalam database dan melakukan upload gambar
    *
    * @param  Request $request
    * @return RedirectResponse
    */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validasi data input dari form
        $validatedData = $request->validate([
            'image'               => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'title'               => 'required|min:5',
            'product_category_id' => 'required|integer', 
            'supplier_id'         => 'required|integer',
            'description'         => 'required|min:10', 
            'price'               => 'required|numeric', 
            'stock'               => 'required|numeric', 
        ]);

        // 2. Handle upload file gambar
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $store_image = $image->store('images', 'public');

            $product = new Product;
            $insertProduct = $product->storeProduct($request, $image);
            
            // 4. Cek hasil insert data
            if ($insertProduct) {
                 // Insert berhasil
                 return redirect()->route('products.index')
                                  ->with('success', 'Data Berhasil Disimpan!');
            }
            
                return redirect()->route('products.index')
                                ->with('error', 'Gagal menyimpan data produk ke database. Silakan coba lagi.');
        }
        
        // 5. Redirect gagal (jika upload gambar gagal atau ada masalah lain)
        return redirect()->route('products.index')
                         ->with('error', 'Failed to upload image (request).');
    }
    // app/Http/Controllers/ProductController.php

    /**
     * show
     * 
     * @param mixed $id
     * @return View
     */
    public function show(string $id): View
    {
        //get product by ID
        $product_model = new Product;
        $product = $product_model->get_product()->where("products.id", $id)->firstOrFail();

        //render view with product
        return view('products.show', compact('product'));
    }

    /**
     * edit
     *
     * @param mixed $id
     * @return View
     */
    public function edit(string $id): View
    {
        $productModel = new Product;
        $data['product'] = $productModel->get_product()->where("products.id", $id)->firstOrFail();

        $categoryModel = new Category_product;
        $product['categories'] = $categoryModel->get_category_product()->get();

        $supplierModel = new Supplier; 
        $product['suppliers_'] = $supplierModel->get_supplier()->get();

        return view('products.edit', compact('data', 'product'));
    }


    /**
     * update
     *
     * @param mixed $request
     * @param mixed $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //validate form
        $request->validate([
            'image'         => 'image|mimes:jpeg,jpg,png|max:2048',
            'title'         => 'required|min:5',
            'description'   => 'required|min:10',
            'price'         => 'required|numeric',
            'stock'         => 'required|numeric'
        ]);

        //get product by ID
        $product_model = new Product;

        $name_image = null;

        //check if image is uploaded
        if ($request->hasFile('image')) {
            //upload new image
            $image = $request->file('image');
            $store_image = $image->store('images', 'public'); // Simpan gambar ke folder penyimpanan
            $name_image = $image->hashName();

            //cari data product berdasarkan id
            $data_product = $product_model->get_product()->where("products.id", $id)->firstOrFail();

            //delete old image
            Storage::disk('public')->delete('images/'.$data_product->image);

            //update product with new image
            $update_product = $product_model->updateProduct($id, $request, $name_image);
        } else {
            $request_data = [
                'title'                 => $request->title,
                'product_category_id'   => $request->product_category_id,
                'supplier_id'           => $request->id_supplier,
                'description'           => $request->description,
                'price'                 => $request->price,
                'stock'                 => $request->stock
            ];
            $update_product = $product_model->updateProduct($id, $request_data);
        }
        //redirect to index
        return redirect()->route('products.index')->with(['success' => 'Data Berhasil Diubah!']);
    }



    /**
     * destroy
     * 
     * @param mixed $id
     * @return RedirectResponse
     */
    public function destroy($id): RedirectResponse
    {
        //get product by ID
        $product_model = new Product;
        $product = $product_model->get_product()->where("products.id", $id)->firstOrFail();

        //delete old image
        Storage::disk('public')->delete('images/'.$product->image);

        //delete product
        $product->delete();

        //redirect to index
        return redirect()->route('products.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }

}
