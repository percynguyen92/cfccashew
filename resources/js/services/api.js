// resources/js/services/api.js
class ApiService {
    constructor() {
        this.baseURL = "/api/v1";
        this.headers = {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                .content,
        };
    }

    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const config = {
            headers: this.headers,
            ...options,
        };

        try {
            const response = await fetch(url, config);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error("API request failed:", error);
            throw error;
        }
    }

    // Bills
    async getBills(params = {}) {
        const query = new URLSearchParams(params).toString();
        return this.request(`/bills?${query}`);
    }

    async getBill(id) {
        return this.request(`/bills/${id}`);
    }

    async createBill(data) {
        return this.request("/bills", {
            method: "POST",
            body: JSON.stringify(data),
        });
    }

    async updateBill(id, data) {
        return this.request(`/bills/${id}`, {
            method: "PUT",
            body: JSON.stringify(data),
        });
    }

    async deleteBill(id) {
        return this.request(`/bills/${id}`, {
            method: "DELETE",
        });
    }

    // Containers
    async getContainers(billId) {
        return this.request(`/bills/${billId}/containers`);
    }

    async createContainer(billId, data) {
        return this.request(`/bills/${billId}/containers`, {
            method: "POST",
            body: JSON.stringify(data),
        });
    }

    async updateContainer(id, data) {
        return this.request(`/containers/${id}`, {
            method: "PUT",
            body: JSON.stringify(data),
        });
    }

    async deleteContainer(id) {
        return this.request(`/containers/${id}`, {
            method: "DELETE",
        });
    }

    // Cutting Tests
    async getBillCuttingTests(billId) {
        return this.request(`/bills/${billId}/cutting-tests`);
    }

    async getContainerCuttingTests(containerId) {
        return this.request(`/containers/${containerId}/cutting-tests`);
    }

    async createCuttingTest(type, id, data) {
        const endpoint =
            type === "bill"
                ? `/bills/${id}/cutting-tests`
                : `/containers/${id}/cutting-tests`;
        return this.request(endpoint, {
            method: "POST",
            body: JSON.stringify(data),
        });
    }

    async updateCuttingTest(id, data) {
        return this.request(`/cutting-tests/${id}`, {
            method: "PUT",
            body: JSON.stringify(data),
        });
    }

    async deleteCuttingTest(id) {
        return this.request(`/cutting-tests/${id}`, {
            method: "DELETE",
        });
    }
}

// Make it globally available
window.api = new ApiService();
