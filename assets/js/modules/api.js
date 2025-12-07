/**
 * API wrapper for Mazi Coffee
 */
import { showModal } from './utils.js';

/**
 * Safe wrapper for Fetch API that handles 401/403 errors and generic network issues.
 * @param {string} url - The URL to fetch.
 * @param {object} options - Fetch options.
 * @returns {Promise<Response>} - The fetch response.
 */
export async function safeFetch(url, options = {}) {
    // MERGE default headers with options.headers
    const headers = {
        'X-Requested-With': 'XMLHttpRequest',
        ...options.headers
    };

    try {
        const res = await fetch(url, { ...options, headers });
        if (!res.ok) {
            if (res.status === 401) {
                // 401 Unauthorized: specific handling if needed
                throw new Error("Unauthorized");
            }
            if (res.status === 403) {
                showModal("Access Denied: You do not have permission.");
                throw new Error("Forbidden");
            }
            throw new Error(`HTTP error! status: ${res.status}`);
        }
        return res;
    } catch (err) {
        console.error("Fetch error:", err);
        // Avoid generic modal for handled auth errors
        if (err.message !== "Unauthorized" && err.message !== "Forbidden") {
            showModal("Connection error. Please check your internet connection and try again.");
        }
        throw err;
    }
}
