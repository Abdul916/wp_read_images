document.getElementById('custom-form').addEventListener('submit', function(e) {
    // Get the value from the extracted text field
    var extractedText = document.getElementById('extracted_text').value;

    // Create a hidden input to send the extracted text with the form
    var extractedTextInput = document.createElement('input');
    extractedTextInput.type = 'hidden';
    extractedTextInput.name = 'extracted_text';
    extractedTextInput.value = extractedText;

    // Append the hidden input to the form before submitting
    this.appendChild(extractedTextInput);
});
