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
    // FUNGSI UNTUK INSERT DATA
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

}
