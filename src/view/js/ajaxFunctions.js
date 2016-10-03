
function ajaxCreateComment(id)
{
    console.log(id);
    var xhr = new XMLHttpRequest();

    var formData = new FormData(document.forms['createComment' + id]);

    xhr.open('POST','/topic/createComment', true);
    xhr.onreadystatechange=function()
    {
        if (xhr.readyState==4 && xhr.status==200) {

            var element = document.getElementById("children"+id);
            element.innerHTML = xhr.responseText +  element.innerHTML;
        }
    }
    xhr.send(formData);
}

function deleteComment(id, topicId) {

    var xhr = new XMLHttpRequest();
    var body = 'id=' + encodeURIComponent(id) + '&topicId=' + encodeURIComponent(topicId);
    xhr.open('POST','/topic/deleteComment', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
    xhr.onreadystatechange=function()
    {
        if (xhr.readyState==4 && xhr.status==200) {
            console.log(xhr.responseText);
            document.getElementById("sub" + id).remove();
        }
    }
    xhr.send(body)
}
