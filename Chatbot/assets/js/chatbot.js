const dfMessenger = document.querySelector('df-messenger');
  
dfMessenger.addEventListener('df-response-received', function (event) {
    const query = event.detail.response.queryResult.queryText;
    const response = event.detail.response.queryResult.fulfillmentText;

    const formData = new FormData();
    formData.append('query', query);
    formData.append('response', response);

    // Path updated to point to the actions folder
    fetch('/Dabbirha/Chatbot/actions/save_chat.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => console.log('Chat Logged:', data))
    .catch(err => console.error('Logging Error:', err));
});