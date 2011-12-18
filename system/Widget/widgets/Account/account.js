function accountAdvices(content) {
    if(content != null) {
        document.querySelector('#advices').style.display = 'block';
        document.querySelector('#advices').innerHTML=content;
    } else
        document.querySelector('#advices').style.display = 'none';
}
