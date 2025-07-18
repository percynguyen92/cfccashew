// Bills SPA module
const API_BASE_URL = "/api/v1";

export default function billsApp() {
    return {
        // State properties
        view: "list", // 'list', 'form', 'detail'
        loading: true,
        globalError: "",

        // List view state
        bills: [],
        pagination: {},

        // Form state
        form: { id: null, billNumber: "", seller: "", buyer: "" },
        errors: {},

        // Detail/Delete state
        currentBill: null,
        billToDelete: null,

        // Query parameters for API requests
        queryParams: {
            search: "",
            sort: "createdAt",
            page: 1,
            filters: {
                seller: "",
            },
        },

        // Initialization
        init() {
            this.handleRouting(); // Set initial view from URL hash
            window.addEventListener("hashchange", () => this.handleRouting());

            // Watch for changes in query params and refetch data
            this.$watch("queryParams", () => {
                this.queryParams.page = 1; // Reset to first page on filter/sort change
                this.updateUrl();
                this.fetchBills();
            });
        },

        // --- API Methods ---

        /**
         * Fetches a paginated list of bills from the API.
         * Constructs the URL from queryParams.
         */
        async fetchBills() {
            this.loading = true;
            this.globalError = "";

            // Example: /api/v1/bills?page=1&sort=-createdAt&search=term&filter[seller][like]=%value%
            const params = new URLSearchParams();
            params.append("page", this.queryParams.page);
            params.append("sort", this.queryParams.sort);
            if (this.queryParams.search) {
                params.append("search", this.queryParams.search);
            }
            if (this.queryParams.filters.seller) {
                params.append(
                    "filter[seller][like]",
                    `%${this.queryParams.filters.seller}%`
                );
            }

            try {
                const response = await fetch(
                    `${API_BASE_URL}/bills?${params.toString()}`
                );
                if (!response.ok) throw new Error("Failed to fetch bills.");
                const result = await response.json();
                this.bills = result.data;
                this.pagination = result.meta;
            } catch (e) {
                this.globalError = e.message;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Fetches a single bill by its ID.
         * Includes related data for the detail view.
         */
        async fetchBill(id) {
            this.loading = true;
            this.globalError = "";
            this.currentBill = null;
            // Example: /api/v1/bills/1?include=containers,containers.cuttingTest
            const include = "containers,containers.cuttingTest";
            try {
                const response = await fetch(
                    `${API_BASE_URL}/bills/${id}?include=${include}`
                );
                if (!response.ok)
                    throw new Error(`Bill with ID ${id} not found.`);
                const result = await response.json();
                this.currentBill = result.data;
            } catch (e) {
                this.globalError = e.message;
                this.changeView("list"); // Go back to list if bill not found
            } finally {
                this.loading = false;
            }
        },

        /**
         * Saves a bill (creates or updates).
         */
        async saveBill() {
            this.loading = true;
            this.errors = {};
            this.globalError = "";

            const isCreating = !this.form.id;
            const url = isCreating
                ? `${API_BASE_URL}/bills`
                : `${API_BASE_URL}/bills/${this.form.id}`;
            const method = isCreating ? "POST" : "PUT";

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                    },
                    body: JSON.stringify(this.form),
                });

                const result = await response.json();

                if (!response.ok) {
                    if (response.status === 422) {
                        // Validation errors
                        this.errors = Object.entries(result.errors).reduce(
                            (acc, [key, value]) => ({
                                ...acc,
                                [key]: value[0],
                            }),
                            {}
                        );
                    } else {
                        throw new Error(result.message || "An error occurred.");
                    }
                } else {
                    this.changeView("list"); // Success, go back to the list
                }
            } catch (e) {
                this.globalError = e.message;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Deletes a bill after confirmation.
         */
        async deleteBill() {
            if (!this.billToDelete) return;

            this.loading = true;
            this.globalError = "";
            try {
                const response = await fetch(
                    `${API_BASE_URL}/bills/${this.billToDelete.id}`,
                    { method: "DELETE" }
                );
                if (!response.ok) throw new Error("Failed to delete the bill.");

                // Remove from list view and close modal
                this.bills = this.bills.filter(
                    (b) => b.id !== this.billToDelete.id
                );
                this.billToDelete = null;
                document.getElementById("delete_modal").close();
            } catch (e) {
                this.globalError = e.message;
            } finally {
                this.loading = false;
            }
        },

        // --- UI & Routing Methods ---

        /**
         * Handles view changes and fetches necessary data.
         */
        changeView(newView, id = null) {
            this.view = newView;
            this.globalError = "";
            this.errors = {}; // Clear errors on view change
            window.location.hash = id ? `${newView}/${id}` : newView;

            if (newView === "list") {
                this.currentBill = null;
                this.form = { id: null, billNumber: "", seller: "", buyer: "" };
                this.fetchBills();
            } else if (newView === "create") {
                this.form = { id: null, billNumber: "", seller: "", buyer: "" };
                this.view = "form";
            } else if (newView === "edit" && id) {
                this.prepareEditForm(id);
            } else if (newView === "detail" && id) {
                this.fetchBill(id);
            }
        },

        /**
         * Prepares the form for editing an existing bill.
         */
        async prepareEditForm(id) {
            // Find bill in existing list to pre-fill form instantly for better UX
            let bill = this.bills.find((b) => b.id === id);
            if (bill) {
                this.form = { ...bill };
                this.view = "form";
            } else {
                // If not in the list (e.g., direct navigation), fetch it
                this.loading = true;
                try {
                    const response = await fetch(`${API_BASE_URL}/bills/${id}`);
                    if (!response.ok) throw new Error("Bill not found");
                    const result = await response.json();
                    this.form = result.data;
                    this.view = "form";
                } catch (e) {
                    this.globalError = e.message;
                    this.changeView("list");
                } finally {
                    this.loading = false;
                }
            }
        },

        /**
         * Simple hash-based router.
         */
        handleRouting() {
            const hash = window.location.hash.replace("#/", "");
            const [view, id] = hash.split("/");

            this.loadStateFromUrl();

            if (
                view === "create" ||
                (view === "edit" && id) ||
                (view === "detail" && id)
            ) {
                this.changeView(view, parseInt(id));
            } else {
                this.changeView("list");
            }
        },

        /**
         * Opens the delete confirmation modal.
         */
        confirmDelete(bill) {
            this.billToDelete = bill;
            document.getElementById("delete_modal").showModal();
        },

        /**
         * Handles pagination clicks.
         */
        gotoPage(page) {
            this.queryParams.page = page;
            this.updateUrl();
            this.fetchBills();
        },

        /**
         * Syncs queryParams state to the browser's URL search string.
         */
        updateUrl() {
            const params = new URLSearchParams(window.location.search);
            params.set("page", this.queryParams.page);
            params.set("sort", this.queryParams.sort);
            params.set("search", this.queryParams.search);
            params.set("seller", this.queryParams.filters.seller);
            // Clean up empty params
            for (const [key, value] of params.entries()) {
                if (!value) params.delete(key);
            }
            history.pushState(
                null,
                "",
                `?${params.toString()}${window.location.hash}`
            );
        },

        /**
         * Loads initial state from URL search parameters on page load.
         */
        loadStateFromUrl() {
            const params = new URLSearchParams(window.location.search);
            this.queryParams.page = parseInt(params.get("page")) || 1;
            this.queryParams.sort = params.get("sort") || "createdAt";
            this.queryParams.search = params.get("search") || "";
            this.queryParams.filters.seller = params.get("seller") || "";
        },
    };
}

// expose globally for alpine initialization in the blade template
window.billsApp = billsApp;
