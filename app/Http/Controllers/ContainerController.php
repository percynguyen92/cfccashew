<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreContainerRequest;
use App\Http\Resources\ContainerResource;
use App\Models\Container;
use App\Services\ContainerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContainerController extends Controller
{
    public function __construct(
        private ContainerService $containerService
    ) {}

    /**
     * Display a listing of containers for a specific bill.
     */
    public function index(Request $request): Response
    {
        $billId = $request->get('bill_id');
        $containers = $billId 
            ? $this->containerService->getContainersByBillId((int) $billId)
            : collect();

        return Inertia::render('Containers/Index', [
            'containers' => ContainerResource::collection($containers),
            'bill_id' => $billId,
        ]);
    }

    /**
     * Show the form for creating a new container.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Containers/Create', [
            'bill_id' => $request->get('bill_id'),
        ]);
    }

    /**
     * Store a newly created container in storage.
     */
    public function store(StoreContainerRequest $request): RedirectResponse
    {
        $container = $this->containerService->createContainer($request->validated());

        return redirect()->route('bills.show', $container->bill_id)
            ->with('success', 'Container created successfully.');
    }

    /**
     * Display the specified container.
     */
    public function show(Container $container): Response
    {
        $containerData = $this->containerService->getContainerById($container->id);

        return Inertia::render('Containers/Show', [
            'container' => new ContainerResource($containerData),
        ]);
    }

    /**
     * Show the form for editing the specified container.
     */
    public function edit(Container $container): Response
    {
        return Inertia::render('Containers/Edit', [
            'container' => new ContainerResource($container),
        ]);
    }

    /**
     * Update the specified container in storage.
     */
    public function update(StoreContainerRequest $request, Container $container): RedirectResponse
    {
        $this->containerService->updateContainer($container, $request->validated());

        return redirect()->route('bills.show', $container->bill_id)
            ->with('success', 'Container updated successfully.');
    }

    /**
     * Remove the specified container from storage.
     */
    public function destroy(Container $container): RedirectResponse
    {
        $billId = $container->bill_id;
        $this->containerService->deleteContainer($container);

        return redirect()->route('bills.show', $billId)
            ->with('success', 'Container deleted successfully.');
    }
}