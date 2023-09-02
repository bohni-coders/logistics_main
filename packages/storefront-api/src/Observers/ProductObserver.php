<?php

namespace Fleetbase\Storefront\Observers;

use Fleetbase\Models\File;
use Fleetbase\FleetOps\Support\Utils;
use Fleetbase\Storefront\Models\Product;
use Fleetbase\Storefront\Models\ProductAddonCategory;
use Fleetbase\Storefront\Models\ProductVariant;
use Fleetbase\Storefront\Models\ProductVariantOption;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Arr;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     *
     * @param  \Fleetbase\Storefront\Models\Product  $product The Product that was created.
     * @return void
     */
    public function created(Product $product): void
    {
        $addonCategories = Request::input('product.addon_categories');
        $variants = Request::input('product.variants');
        $files = Request::input('product.files');

        // save addon categories
        foreach ($addonCategories as $addonCategory) {
            $addonCategory['product_uuid'] = $product->uuid;

            ProductAddonCategory::create(Arr::except($addonCategory, ['category']));
        }

        // save product variants
        foreach ($variants as $variant) {
            $variant['created_by_uuid'] = Request::session()->get('user');
            $variant['company_uuid'] = Request::session()->get('company');
            $variant['product_uuid'] = $product->uuid;

            $productVariant = ProductVariant::create(Arr::except($variant, ['options']));

            foreach ($variant['options'] as $option) {
                $option['product_variant_uuid'] = $productVariant->uuid;
                ProductVariantOption::create($option);
            }
        }

        // set keys on files
        foreach ($files as $file) {
            $fileRecord = File::where('uuid', $file['uuid'])->first();
            $fileRecord->setKey($product);
        }
    }

    /**
     * Handle the Product "updated" event.
     *
     * @param  \Fleetbase\Storefront\Models\Product  $product The Product that was created.
     * @return void
     */
    public function updated(Product $product): void
    {
        $productAddonCategories = Request::input('product.addon_categories');
        $variants = Request::input('product.variants');

        // update addon categories
        foreach ($productAddonCategories as $productAddonCategory) {
            if (!empty($productAddonCategory['uuid'])) {
                ProductAddonCategory::where('uuid', $productAddonCategory['uuid'])->update(Arr::except($productAddonCategory, ['uuid', 'name', 'category']));
                continue;
            }

            // add new addon category
            $productAddonCategory['product_uuid'] = $product->uuid;
            ProductAddonCategory::create(Arr::except($productAddonCategory, ['category']));
        }

        // update product variants
        foreach ($variants as $variant) {
            if (!empty($variant['uuid'])) {
                // update product variante
                ProductVariant::where('uuid', $variant['uuid'])->update(Arr::except($variant, ['uuid', 'options']));

                // update product variant options
                foreach ($variant['options'] as $option) {
                    if (!empty($option['uuid'])) {
                        // make sure additional cost is always numbers only
                        if (isset($option['additional_cost'])) {
                            $option['additional_cost'] = Utils::numbersOnly($option['additional_cost']);
                        }

                        $updateAttrs = Arr::except($option, ['uuid']);

                        ProductVariantOption::where('uuid', $option['uuid'])->update($updateAttrs);
                        continue;
                    }

                    $option['product_variant_uuid'] = $variant['uuid'];
                    ProductVariantOption::create($option);
                }
                continue;
            }

            // create new variant
            $variant['created_by_uuid'] = Request::session()->get('user');
            $variant['company_uuid'] = Request::session()->get('company');
            $variant['product_uuid'] = $product->uuid;

            $productVariant = ProductVariant::create(Arr::except($variant, ['options']));

            foreach ($variant['options'] as $option) {
                $option['product_variant_uuid'] = $productVariant->uuid;
                ProductVariantOption::create($option);
            }
        }
    }
}
