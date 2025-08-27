import Carousel from "@/Components/Core/Carousel";
import CurrencyFormatter from "@/Components/Core/CurrencyFormatter";
import { arraysAreEqual } from "@/helpers";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Product, VariationTypeOption } from "@/types";
import { Head, router, useForm, usePage } from "@inertiajs/react";
import React, { useEffect, useMemo, useState } from "react";

type Form = {
    option_ids: Record<string, number>;
    quantity: number;
    price: number | null;
};

function Show({
    product,
    variationOptions,
}: {
    product: Product;
    variationOptions: Record<number, number> | number[];
}) {
    
    const form = useForm<Form>({
        option_ids: {},
        quantity: 1,
        price: null,
    });

    const { url } = usePage();
    // Default harus object kosong, bukan array
    const [selectedOptions, setSelectedOptions] = useState<
        Record<number, VariationTypeOption>
    >([]);

    const images = useMemo(() => {
        for (let typeId in selectedOptions) {
            const option = selectedOptions[typeId];
            if (option?.images?.length > 0) return option.images;
        }
        return product.images;
    }, [product, selectedOptions]);

    const computedProduct = useMemo(() => {
        const selectedOptionIds = Object.values(selectedOptions)
            .map((option) => option.id)
            .sort();
        for (let variation of product.variations) {
            const optionIds = variation.variation_type_option_ids.sort();
            if (arraysAreEqual(selectedOptionIds, optionIds)) {
                return {
                    price: variation.price,
                    quantity:
                        variation.quantity === null
                            ? Number.MAX_VALUE
                            : variation.quantity,
                };
            }
        }
        return {
            price: product.price,
            quantity: product.quantity,
        };
    }, [product, selectedOptions]);

    // Pastikan hanya jalan sekali saat mount, atau saat product berubah
    useEffect(() => {
        if (Object.keys(selectedOptions).length === 0) {
        product.variationTypes.forEach((type, idx) => {
            let selectedOptionId: number | undefined;
            if (Array.isArray(variationOptions)) {
                selectedOptionId = variationOptions[idx];
            } else if (variationOptions && typeof variationOptions === "object") {
                selectedOptionId = variationOptions[type.id];
            }
            const found = type.options.find(opt => opt.id === selectedOptionId) || type.options[0];
            chooseOption(type.id, found, false);
        });
    }
    }, [product, variationOptions]);

    const getOptionIdsMap = (
        newOptions: Record<string | number, VariationTypeOption>
    ) => {
        return Object.fromEntries(
            Object.entries(newOptions).map(([a, b]) => [a, b.id])
        );
    };

    const chooseOption = (
        typeId: number,
        option: VariationTypeOption,
        updateRouter: boolean = true
    ) => {
        setSelectedOptions((prev) => {
            const newOptions = {
                ...prev,
                [typeId]: option,
            };
            if (updateRouter) {
                router.get(
                    url,
                    {
                        options: getOptionIdsMap(newOptions),
                    },
                    {
                        preserveScroll: true,
                        preserveState: true,
                    }
                );
            }
            return newOptions;
        });
    };

    const onQuantityChange = (event: React.ChangeEvent<HTMLSelectElement>) => {
        form.setData("quantity", parseInt(event.target.value));
    };

    const addToCart = () => {
        form.post(route("cart.store", product.id), {
            preserveScroll: true,
            preserveState: true,
            onError: (err) => {
                console.log(err);
            },
        });
    };

    const renderProductVariationTypes = () => {
        return product.variationTypes.map((type) => (
            <div key={type.id}>
                <b>{type.name}</b>
                {type.type === "Image" && (
                    <div className="flex gap-2 mb-4">
                        {type.options.map((option) => (
                            <div
                                onClick={() => chooseOption(type.id, option)}
                                key={option.id}
                            >
                                {option.images?.[0]?.thumb && (
                                    <img
                                        src={option.images[0].thumb}
                                        alt=""
                                        className={
                                            "w-[50px] " +
                                            (selectedOptions[type.id]?.id ===
                                            option.id
                                                ? "outline outline-4 outline-primary"
                                                : "")
                                        }
                                    />
                                )}
                            </div>
                        ))}
                    </div>
                )}
                <div className="flex flex-col">

                {type.type === "Radio" && (
                    <div className="inline join mb-4">
                        {type.options.map((option) => (
                            <input
                            onChange={() => chooseOption(type.id, option)}
                            key={option.id}
                            className="join-item btn"
                            type="radio"
                            checked={
                                selectedOptions[type.id]?.id === option.id
                            }
                            name={"variation_type_" + type.id}
                            aria-label={option.name}
                            />
                        ))}
                    </div>
                )}
                </div>
            </div>
        ));
    };

    useEffect(() => {
        const idsMap = Object.fromEntries(
            Object.entries(selectedOptions).map(([typeId, option]) => [
                typeId,
                option.id,
            ])
        );
        form.setData("option_ids", idsMap);
    }, [selectedOptions]);

    const renderAddToCartButton = () => {
        return (
            <div className="mb-8 flex gap-4">
                <select
                    value={form.data.quantity}
                    onChange={onQuantityChange}
                    className="select select-bordered w-full"
                >
                    {Array.from({
                        length: Math.min(10, computedProduct.quantity) || 0,
                    }).map((_, index) => (
                        <option value={index + 1} key={index + 1}>
                            Quantity: {index + 1}
                        </option>
                    ))}
                </select>
                <button onClick={addToCart} className="btn btn-primary">
                    Add to Cart
                </button>
            </div>
        );
    };

    return (
        <AuthenticatedLayout>
            <Head title={product.title} />
            <div className="container mx-auto p-8">
                <div className="grid gap-8 grid-cols-1 lg:grid-cols-12 items-start">
                    {/* Kolom kiri */}
                    <div className="lg:col-span-7">
                        <Carousel images={images} />
                    </div>

                    {/* Kolom kanan */}
                    <div className="lg:col-span-5 flex flex-col gap-4">
                        <h1 className="text-2xl">{product.title}</h1>

                        <div className="text-3xl font-semibold">
                            <CurrencyFormatter amount={computedProduct.price} />
                        </div>

                        {/* <pre>
                            {JSON.stringify(product.variationTypes, undefined, 2)}
                        </pre> */}

                        {renderProductVariationTypes()}

                        {computedProduct.quantity !== undefined &&
                            computedProduct.quantity < 10 && (
                                <div className="text-error">
                                    Only {computedProduct.quantity} left
                                </div>
                            )}

                        {renderAddToCartButton()}

                        <b className="text-xl">About the Item</b>
                        <div
                            className="wysiwyg-output"
                            dangerouslySetInnerHTML={{
                                __html: product.description,
                            }}
                        />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

export default Show;
