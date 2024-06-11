// Select DOM elements
const inputBox = document.querySelector(".input-box");
const searchIcon = document.querySelector(".icon");
const closeIcon = document.querySelector(".close-icon");
const searchInput = document.getElementById("searchInput");
const searchResults = document.getElementById("searchResults");

// Event listener to open the search box
searchIcon.addEventListener("click", () => {
    inputBox.classList.add("open");
    searchInput.focus(); // Focus on the input field when opened
});

// Event listener to close the search box and clear content
closeIcon.addEventListener("click", () => {
    inputBox.classList.remove("open");
    searchInput.value = ""; // Clear input field
    searchResults.innerHTML = ""; // Clear search results
});

// Event listener for input changes in the search field
searchInput.addEventListener("input", performSearch);

// Function to handle search logic
function performSearch() {
    const query = searchInput.value.trim().toLowerCase();
    searchResults.innerHTML = ""; // Clear previous search results

    // Perform search only if query is not empty
    if (query) {
        // Placeholder: Displaying dummy search results with highlighted matches
        let searchResultsHTML = "";
        let hasResults = false; // Assume no results initially

        // Placeholder: Simulating search results for demonstration
        const dummyResults = ["Search Result 1", "Search Result 2", "Search Result 3", "Search Result 4", "Search Result 5"];
        const filteredResults = dummyResults.filter(result => result.toLowerCase().includes(query));
        hasResults = filteredResults.length > 0;

        if (hasResults) {
            // Display actual search results
            filteredResults.forEach(result => {
                const highlightedText = result.replace(new RegExp(query, "gi"), (match) => `<mark>${match}</mark>`);
                searchResultsHTML += `<p>${highlightedText}</p>`;
            });
        } else {
            // Display "No Results Found" message
            searchResultsHTML = "<p>No Results Found</p>";
        }

        searchResults.innerHTML = searchResultsHTML;
    }
}
