// resources/js/utils/performance.js
class PerformanceMonitor {
    constructor() {
        this.metrics = {};
    }

    startTimer(name) {
        this.metrics[name] = {
            start: performance.now(),
        };
    }

    endTimer(name) {
        if (this.metrics[name]) {
            this.metrics[name].duration =
                performance.now() - this.metrics[name].start;
            console.log(`${name}: ${this.metrics[name].duration.toFixed(2)}ms`);
        }
    }

    measureApiCall(url, options = {}) {
        const startTime = performance.now();

        return fetch(url, options).then((response) => {
            const duration = performance.now() - startTime;
            console.log(`API Call ${url}: ${duration.toFixed(2)}ms`);
            return response;
        });
    }

    observeIntersection(elements, callback) {
        if ("IntersectionObserver" in window) {
            const observer = new IntersectionObserver(callback, {
                threshold: 0.1,
            });

            elements.forEach((element) => observer.observe(element));
            return observer;
        }
    }
}

window.performanceMonitor = new PerformanceMonitor();
