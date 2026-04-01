// assets/js/faqs_scripts.js

document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const searchInput = document.querySelector('.search-input');
    const accordionItems = document.querySelectorAll('.accordion-item');
    const accordionButtons = document.querySelectorAll('.accordion-button');
    const accordionCollapses = document.querySelectorAll('.accordion-collapse');
    
    // Check if we're in Arabic mode
    const isRTL = document.documentElement.dir === 'rtl';
    
    // Debounce function to limit search frequency
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Function to highlight search terms in text
    function highlightText(text, searchTerm) {
        if (!searchTerm.trim()) return text;
        
        const regex = new RegExp(`(${searchTerm})`, 'gi');
        return text.replace(regex, '<mark class="search-highlight">$1</mark>');
    }
    
    // Function to check if text contains search term
    function containsText(text, searchTerm) {
        if (!searchTerm.trim()) return true;
        return text.toLowerCase().includes(searchTerm.toLowerCase());
    }
    
    // Main search function
    function performSearch() {
        const searchTerm = searchInput.value.trim();
        
        if (searchTerm.length === 0) {
            // Show all items if search is empty
            accordionItems.forEach(item => {
                item.style.display = 'block';
            });
            
            // Remove all highlights
            document.querySelectorAll('.search-highlight').forEach(highlight => {
                const parent = highlight.parentNode;
                parent.replaceChild(document.createTextNode(highlight.textContent), highlight);
                parent.normalize();
            });
            
            return;
        }
        
        let foundAny = false;
        
        accordionItems.forEach((item, index) => {
            const button = item.querySelector('.accordion-button');
            const body = item.querySelector('.accordion-body');
            const buttonText = button.textContent || button.innerText;
            const bodyText = body.textContent || body.innerText;
            
            // Check if search term is found in either question or answer
            const matchesQuestion = containsText(buttonText, searchTerm);
            const matchesAnswer = containsText(bodyText, searchTerm);
            
            if (matchesQuestion || matchesAnswer) {
                item.style.display = 'block';
                foundAny = true;
                
                // Expand the accordion item
                const collapseId = button.getAttribute('data-bs-target').replace('#', '');
                const collapseElement = document.getElementById(collapseId);
                if (collapseElement) {
                    button.classList.remove('collapsed');
                    collapseElement.classList.add('show');
                }
                
                // Highlight the search term
                if (matchesQuestion) {
                    const newButtonHTML = highlightText(buttonText, searchTerm);
                    // Preserve the icon
                    const icon = button.querySelector('i');
                    if (icon) {
                        button.innerHTML = icon.outerHTML + ' ' + newButtonHTML;
                    } else {
                        button.innerHTML = newButtonHTML;
                    }
                }
                
                if (matchesAnswer) {
                    const originalBodyHTML = body.getAttribute('data-original-html') || body.innerHTML;
                    body.setAttribute('data-original-html', originalBodyHTML);
                    body.innerHTML = highlightText(bodyText, searchTerm);
                }
                
            } else {
                item.style.display = 'none';
                // Restore original content
                const buttonIcon = button.querySelector('i');
                const originalButtonText = buttonText.replace(/\s+/g, ' ').trim();
                button.innerHTML = buttonIcon ? buttonIcon.outerHTML + ' ' + originalButtonText : originalButtonText;
                
                const bodyOriginal = body.getAttribute('data-original-html');
                if (bodyOriginal) {
                    body.innerHTML = bodyOriginal;
                }
            }
        });
        
        // Show "no results" message if needed
        showNoResultsMessage(foundAny, searchTerm);
    }
    
    // Show/hide "no results" message
    function showNoResultsMessage(foundAny, searchTerm) {
        let noResultsDiv = document.getElementById('no-results-message');
        
        if (!foundAny && searchTerm.length > 0) {
            if (!noResultsDiv) {
                noResultsDiv = document.createElement('div');
                noResultsDiv.id = 'no-results-message';
                noResultsDiv.className = 'text-center py-5';
                noResultsDiv.innerHTML = `
                    <div class="mb-3">
                        <i class="ri-search-eye-line fs-1 text-muted"></i>
                    </div>
                    <h4 class="mb-2">${isRTL ? 'لم يتم العثور على نتائج' : 'No results found'}</h4>
                    <p class="text-muted">
                        ${isRTL ? 
                            `لم نتمكن من العثور على أي نتائج لـ "<strong>${searchTerm}</strong>". حاول البحث بكلمات أخرى.` : 
                            `We couldn't find any results for "<strong>${searchTerm}</strong>". Try searching with different keywords.`
                        }
                    </p>
                `;
                
                const accordion = document.getElementById('dabbirhaFaq');
                accordion.parentNode.insertBefore(noResultsDiv, accordion.nextSibling);
            }
        } else if (noResultsDiv) {
            noResultsDiv.remove();
        }
    }
    
    // Clear search and restore everything
    function clearSearch() {
        searchInput.value = '';
        performSearch();
        searchInput.focus();
    }
    
    // Add clear button functionality
    function setupClearButton() {
        // Create clear button
        const clearBtn = document.createElement('button');
        clearBtn.type = 'button';
        clearBtn.className = 'btn btn-link text-muted p-0';
        clearBtn.innerHTML = '<i class="ri-close-line"></i>';
        clearBtn.style.cssText = 'position: absolute; top: 50%; transform: translateY(-50%); right: 15px; z-index: 10; background: transparent; border: none;';
        
        if (isRTL) {
            clearBtn.style.right = 'auto';
            clearBtn.style.left = '15px';
        }
        
        // Insert clear button
        const searchContainer = searchInput.parentElement;
        searchContainer.style.position = 'relative';
        searchContainer.appendChild(clearBtn);
        
        // Add click event
        clearBtn.addEventListener('click', clearSearch);
        
        // Show/hide clear button based on input
        searchInput.addEventListener('input', function() {
            clearBtn.style.display = this.value ? 'block' : 'none';
        });
        
        // Initial state
        clearBtn.style.display = searchInput.value ? 'block' : 'none';
    }
    
    // Add CSS for highlighting
    const style = document.createElement('style');
    style.textContent = `
        .search-highlight {
            background-color: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: 600;
        }
        
        .accordion-item {
            transition: all 0.3s ease;
        }
        
        /* Animation for search results */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .accordion-item[style*="display: block"] {
            animation: fadeIn 0.3s ease forwards;
        }
    `;
    document.head.appendChild(style);
    
    // Initialize
    setupClearButton();
    
    // Event listener for search input with debounce
    searchInput.addEventListener('input', debounce(performSearch, 300));
    
    // Also search on Enter key
    searchInput.addEventListener('keyup', function(event) {
        if (event.key === 'Enter') {
            performSearch();
        }
    });
    
    // Store original content for each accordion body
    accordionItems.forEach((item, index) => {
        const body = item.querySelector('.accordion-body');
        if (body) {
            body.setAttribute('data-original-html', body.innerHTML);
        }
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(event) {
        // Clear search with Escape key
        if (event.key === 'Escape' && searchInput.value) {
            clearSearch();
            event.preventDefault();
        }
        
        // Focus search with Ctrl/Cmd + F
        if ((event.ctrlKey || event.metaKey) && event.key === 'f') {
            event.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
    });
    
    // Make FAQ items clickable to collapse/expand
    accordionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const collapseId = this.getAttribute('data-bs-target').replace('#', '');
            const collapseElement = document.getElementById(collapseId);
            
            if (collapseElement.classList.contains('show')) {
                this.classList.add('collapsed');
                collapseElement.classList.remove('show');
            } else {
                this.classList.remove('collapsed');
                collapseElement.classList.add('show');
            }
        });
    });
});