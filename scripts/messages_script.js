const Messages = {
    spinner: document.getElementById('spinner'),
    formElement: document.getElementById('messaging-form'),
    message: document.getElementById('message-input'), 
    messages: document.getElementById('messages-list'),
    
    addMessage(message, local) {
        var li = document.createElement("li");
        li.appendChild(document.createTextNode(message));
        li.classList.add(local ? "ml-auto" : "mr-auto");
        li.classList.add("message");
        li.classList.add("m-2");
        li.classList.add("p-2");
        this.messages.appendChild(li);
    },
    async init(){
        this.formElement.onsubmit = e => {
            e.preventDefault();
        };
        this.fetch(to);
    },
    async fetch(to){ 
        this.spinner.classList.add('loader');
        while (this.messages.firstChild)
            this.messages.removeChild(this.messages.lastChild);
        fetch(`/backend/messages.php?i=${to}`)
            .then(response => {
                if (response.status == 200) {
                    response.json().then(data => 
                        data.messages.forEach(message => {
                            this.addMessage(message.text, message.to == to);
                        })
                    );
                }
                this.spinner.classList.remove('loader');
            })
            .catch(error =>  { 
               console.log('Request failed', error) 
            });
    },
    async send(){
        messageText = this.message.value;
        if (messageText == '')
            return;
        fetch(`/backend/messages.php?i=${to}`,
            { 
                method: 'post',
                body: new FormData(this.formElement)
            })
            .then(response => {
                if (response.status === 201) {
                    this.addMessage(messageText, true);
                    this.message.value = '';
                }
                else response.json().then(data => console.log(data.error));
            })
            .catch(error =>  { 
               console.log('Request failed', error) 
            });
    },
    async switchTo(id) {
        to = id;
        this.fetch(to);
    }
}

Messages.init();