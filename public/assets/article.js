document.querySelectorAll('button.More').forEach(bttn=>{
    bttn.dataset.state=0;
    bttn.addEventListener('click',function(e){

        let firstP = this.closest('.article').getElementsByTagName('p')[1];
        let anotherP = this.closest('.article').querySelectorAll('p:nth-child(n+4)')
        let articlePhotos = this.closest('.article').getElementsByClassName('article-photo-row')[0]

        if (this.dataset.state === '1') {
            anotherP.forEach(p => {
                p.style.display = "none";
            });
            articlePhotos.style.display = "none";
        } else {
            anotherP.forEach(p => {
                p.style.display = "";
            });
            articlePhotos.style.display = "";
        }

        if (anotherP.length > 0 || articlePhotos.innerHTML.trim() !== "") {
            this.innerHTML = this.dataset.state === '1' ? 'Číst více...' : 'Číst méně...';
        }
        this.dataset.state = 1 - this.dataset.state;
    })
});

document.querySelectorAll('.More').forEach(btn => {
    let anotherP = btn.closest('.article').querySelectorAll('p:nth-child(n+4)')
    let articlePhotos = btn.closest('.article').getElementsByClassName('article-photo-row')[0];

    anotherP.forEach(p=> {
        p.style.display = "none";
    })
    articlePhotos.style.display = "none";

    if (anotherP.length === 0 && articlePhotos.innerHTML.trim() === "") {
        btn.style.display = "none";
    }
})