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
     * Display a listing of all containers with pagination and search.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only([
            'container_number',
            'truck',
            'bill_info',
            'date_from',
            'date_to',
        ]);

        $containers = $this->containerService->getAllContainersPaginated($filters, 15);
        $containerItems = ContainerResource::collection($containers->items())
            ->resolve();
        $activeFilters = array_filter(
            $filters,
            fn ($value) => $value !== null && $value !== ''
        );

        return Inertia::render('Containers/Index', [
            'containers' => $containerItems,
            'pagination' => [
                'current_page' => $containers->currentPage(),
                'last_page' => $containers->lastPage(),
                'per_page' => $containers->perPage(),
                'total' => $containers->total(),
                'from' => $containers->firstItem(),
                'to' => $containers->lastItem(),
                'links' => $containers->linkCollection(),
            ],
            'filters' => $activeFilters,
        ]);
    }

    /**
     * Show the form for creating a new container.
     */
    public function create(Request $request): Response
    {
        $billId = $request->get('bill_id');
        $bill = null;
        
        if ($billId) {
            $bill = \App\Models\Bill::find($billId);
        }

        return Inertia::render('Containers/Create', [
            'bill_id' => $billId,
            'bill' => $bill ? new \App\Http\Resources\BillResource($bill) : null,
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
        // Load relationships if not already loaded
        if (!$container->relationLoaded('cuttingTests') || !$container->relationLoaded('bill')) {
            $container->load(['cuttingTests', 'bill']);
        }

        return Inertia::render('Containers/Show', [
            'container' => (new ContainerResource($container))->resolve(),
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

