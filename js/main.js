// Function to make AJAX requests
function ajaxRequest(url, method, data, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            callback(xhr.responseText);
        }
    };
    xhr.send(data);
}

// Example function to add a book using AJAX
function addBook(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    const data = new URLSearchParams(formData).toString();

    ajaxRequest('books/add.php', 'POST', data, function(response) {
        alert(response);
        form.reset();
    });
}

// Example function to delete a book using AJAX
function deleteBook(bookId) {
    if (confirm('Are you sure you want to delete this book?')) {
        ajaxRequest('books/delete.php', 'POST', `id=${bookId}`, function(response) {
            alert(response);
            // Reload the book list or remove the deleted book from the DOM
        });
    }
}

// Add event listeners when the DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const addBookForm = document.getElementById('add-book-form');
    if (addBookForm) {
        addBookForm.addEventListener('submit', addBook);
    }

    const deleteButtons = document.querySelectorAll('.delete-book');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            deleteBook(this.dataset.id);
        });
    });
});