const API_BASE_URL = "/api/v1";

function billsApp() {
    return {
        // States
        view: "list", // 'list', 'form', 'detail'
        loading: false,
        globalError: "",
        showDeleteModal: false,

        bills: [],
        pagination: {
            current_page: 1,
            last_page: 1,
            from: 0,
            to: 0,
            total: 0,
        },

        form: { id: null, billNumber: "", seller: "", buyer: "" },
        errors: {},

        currentBill: null,
        billToDelete: null,

        queryParams: {
            search: "",
            sort: "createdAt",
            page: 1,
            filters: {
                seller: "",
            },
        },

        init() {
            this.handleRouting();
            window.addEventListener("hashchange", () => this.handleRouting());
            this.loadStateFromUrl();

            this.$watch("queryParams.search", () => {
                this.queryParams.page = 1;
                this.updateUrl();
                this.debouncedFetchBills();
            });

            this.$watch("queryParams.sort", () => {
                this.queryParams.page = 1;
                this.updateUrl();
                this.fetchBills();
            });

            if (this.view === "list") {
                this.fetchBills();
            }
        },

        debouncedFetchBills() {
            clearTimeout(this.fetchTimeout);
            this.fetchTimeout = setTimeout(() => {
                this.fetchBills();
            }, 500);
        },

        getPageTitle() {
            switch (this.view) {
                case "list":
                    return "Bills Management";
                case "form":
                    return this.form.id ? "Sửa Bill" : "Tạo Bill mới";
                case "detail":
                    return "Chi tiết Bill";
                default:
                    return "Bills Management";
            }
        },

        getPaginationPages() {
            const pages = [];
            const current = this.queryParams.page;
            const total = this.pagination.last_page || 1;
            const delta = 2;

            for (
                let i = Math.max(1, current - delta);
                i <= Math.min(total, current + delta);
                i++
            ) {
                pages.push(i);
            }

            return pages;
        },

        async fetchBills() {
            this.loading = true;
            this.globalError = "";

            try {
                const params = new URLSearchParams();
                params.append("page", this.queryParams.page);
                params.append("sort", this.queryParams.sort);

                if (this.queryParams.search.trim()) {
                    params.append("search", this.queryParams.search.trim());
                }

                const response = await fetch(
                    `${API_BASE_URL}/bills?${params.toString()}`
                );
                if (!response.ok) {
                    throw new Error(
                        `HTTP ${response.status}: Failed to fetch bills`
                    );
                }
                const result = await response.json();
                this.bills = result.data || [];
                this.pagination = result.meta || this.pagination;
            } catch (error) {
                this.globalError = error.message || "Failed to load bills.";
                this.bills = [];
            } finally {
                this.loading = false;
            }
        },

        async fetchBill(id) {
            if (!id) return;

            this.loading = true;
            this.globalError = "";
            this.currentBill = null;

            try {
                const include = "containers,containers.cuttingTest";
                const response = await fetch(
                    `${API_BASE_URL}/bills/${id}?include=${include}`
                );
                if (!response.ok) {
                    throw new Error(`Bill with ID ${id} not found`);
                }
                const result = await response.json();
                this.currentBill = result.data;
            } catch (error) {
                this.globalError =
                    error.message || "Failed to load bill details";
                this.changeView("list");
            } finally {
                this.loading = false;
            }
        },

        async saveBill() {
            this.loading = true;
            this.errors = {};
            this.globalError = "";

            if (!this.form.billNumber.trim()) {
                this.errors.billNumber = "Bill number is required";
            }
            if (!this.form.seller.trim()) {
                this.errors.seller = "Seller is required";
            }
            if (!this.form.buyer.trim()) {
                this.errors.buyer = "Buyer is required";
            }
            if (Object.keys(this.errors).length > 0) {
                this.loading = false;
                return;
            }

            const isCreating = !this.form.id;
            const url = isCreating
                ? `${API_BASE_URL}/bills`
                : `${API_BASE_URL}/bills/${this.form.id}`;
            const method = isCreating ? "POST" : "PUT";

            try {
                const response = await fetch(url, {
                    method,
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                    },
                    body: JSON.stringify({
                        billNumber: this.form.billNumber.trim(),
                        seller: this.form.seller.trim(),
                        buyer: this.form.buyer.trim(),
                    }),
                });

                const result = await response.json();

                if (!response.ok) {
                    if (response.status === 422 && result.errors) {
                        this.errors = Object.entries(result.errors).reduce(
                            (acc, [key, messages]) => {
                                acc[key] = Array.isArray(messages)
                                    ? messages[0]
                                    : messages;
                                return acc;
                            },
                            {}
                        );
                    } else {
                        throw new Error(
                            result.message || "Failed to save bill"
                        );
                    }
                } else {
                    this.changeView("list");
                    this.fetchBills();
                }
            } catch (error) {
                this.globalError = error.message || "Failed to save bill.";
            } finally {
                this.loading = false;
            }
        },

        async deleteBill() {
            if (!this.billToDelete?.id) return;

            this.loading = true;
            this.globalError = "";

            try {
                const response = await fetch(
                    `${API_BASE_URL}/bills/${this.billToDelete.id}`,
                    {
                        method: "DELETE",
                        headers: { Accept: "application/json" },
                    }
                );
                if (!response.ok) {
                    const result = await response.json().catch(() => ({}));
                    throw new Error(result.message || "Failed to delete bill");
                }

                this.bills = this.bills.filter(
                    (b) => b.id !== this.billToDelete.id
                );
                this.showDeleteModal = false;
                this.billToDelete = null;

                if (this.bills.length === 0 && this.queryParams.page > 1) {
                    this.queryParams.page--;
                    this.fetchBills();
                }
            } catch (error) {
                this.globalError = error.message || "Failed to delete bill.";
            } finally {
                this.loading = false;
            }
        },

        changeView(newView, id = null) {
            this.globalError = "";
            this.errors = {};
            this.loading = false;

            if (newView === "list") {
                this.view = "list";
                this.currentBill = null;
                this.resetForm();
                window.location.hash = "";
                this.fetchBills();
            } else if (newView === "create") {
                this.view = "form";
                this.resetForm();
                window.location.hash = "create";
            } else if (newView === "edit" && id) {
                this.view = "form";
                window.location.hash = `edit/${id}`;
                this.prepareEditForm(id);
            } else if (newView === "detail" && id) {
                this.view = "detail";
                window.location.hash = `detail/${id}`;
                this.fetchBill(id);
            } else {
                this.changeView("list");
            }
        },

        resetForm() {
            this.form = { id: null, billNumber: "", seller: "", buyer: "" };
            this.errors = {};
        },

        async prepareEditForm(id) {
            if (!id) return;

            const existingBill = this.bills.find((b) => b.id === parseInt(id));
            if (existingBill) {
                this.form = {
                    id: existingBill.id,
                    billNumber: existingBill.billNumber || "",
                    seller: existingBill.seller || "",
                    buyer: existingBill.buyer || "",
                };
            } else {
                this.loading = true;
                try {
                    const response = await fetch(`${API_BASE_URL}/bills/${id}`);
                    if (!response.ok) throw new Error("Bill not found");
                    const result = await response.json();
                    const bill = result.data;
                    this.form = {
                        id: bill.id,
                        billNumber: bill.billNumber || "",
                        seller: bill.seller || "",
                        buyer: bill.buyer || "",
                    };
                } catch (error) {
                    this.globalError =
                        error.message || "Failed to load bill for editing";
                    this.changeView("list");
                } finally {
                    this.loading = false;
                }
            }
        },

        handleRouting() {
            const hash = window.location.hash.replace("#", "");
            if (!hash) {
                if (this.view !== "list") this.changeView("list");
                return;
            }
            const [viewName, id] = hash.split("/");
            const numericId = id ? parseInt(id) : null;

            switch (viewName) {
                case "create":
                    if (this.view !== "form" || this.form.id !== null)
                        this.changeView("create");
                    break;
                case "edit":
                    if (
                        numericId &&
                        (this.view !== "form" || this.form.id !== numericId)
                    )
                        this.changeView("edit", numericId);
                    break;
                case "detail":
                    if (
                        numericId &&
                        (this.view !== "detail" ||
                            this.currentBill?.id !== numericId)
                    )
                        this.changeView("detail", numericId);
                    break;
                default:
                    this.changeView("list");
            }
        },

        confirmDelete(bill) {
            this.billToDelete = bill;
            this.showDeleteModal = true;
        },

        gotoPage(page) {
            const pageNum = parseInt(page);
            if (
                pageNum < 1 ||
                pageNum > this.pagination.last_page ||
                pageNum === this.queryParams.page
            )
                return;

            this.queryParams.page = pageNum;
            this.updateUrl();
            this.fetchBills();

            document
                .querySelector("main")
                .scrollIntoView({ behavior: "smooth" });
        },

        updateUrl() {
            const params = new URLSearchParams(window.location.search);
            params.set("page", this.queryParams.page);
            params.set("sort", this.queryParams.sort);

            if (this.queryParams.search.trim()) {
                params.set("search", this.queryParams.search.trim());
            } else {
                params.delete("search");
            }

            const newUrl = `${window.location.pathname}?${params.toString()}${
                window.location.hash
            }`;
            history.replaceState(null, "", newUrl);
        },

        loadStateFromUrl() {
            const params = new URLSearchParams(window.location.search);
            this.queryParams.page = Math.max(
                1,
                parseInt(params.get("page")) || 1
            );
            this.queryParams.sort = params.get("sort") || "createdAt";
            this.queryParams.search = params.get("search") || "";
            this.queryParams.filters.seller = params.get("seller") || "";
        },
    };
}

window.billsApp = billsApp;
