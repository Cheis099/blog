document.addEventListener('DOMContentLoaded', function() {
    const burger = document.querySelector('.burger');
    const nav = document.querySelector('nav ul');

    if (burger && nav) {
        burger.addEventListener('click', function() {
            nav.classList.toggle('nav-active');
            burger.classList.toggle('toggle');
        });
    }

    const commentForm = document.getElementById('comment-form');
    const likeBtn = document.getElementById('like-btn');
    const likeIcon = document.getElementById('like-icon');
    const likesCount = document.getElementById('likes-count');

    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(commentForm);
            const content = formData.get('content');
            if (!content.trim()) {
                alert('Пожалуйста, введите комментарий.');
                return;
            }
            fetch('ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const commentsList = document.querySelector('.comments-list');
                    const noComments = commentsList.querySelector('p');
                    if (noComments && noComments.textContent.includes('Комментариев пока нет')) {
                        noComments.remove();
                    }
                    const newComment = document.createElement('div');
                    newComment.classList.add('comment');
                    newComment.innerHTML =
                        '<div class="comment-author"><strong>' + data.comment.username + '</strong> ' +
                        '<span class="comment-date">' + data.comment.created_at + '</span></div>' +
                        '<div class="comment-content">' + data.comment.content.replace(/\n/g, '<br>') + '</div>';
                    commentsList.insertBefore(newComment, commentsList.firstChild);
                    commentForm.reset();
                } else {
                    alert('Ошибка: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла ошибка при отправке.');
            });
        });
    }

    if (likeBtn) {
        likeBtn.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const formData = new FormData();
            formData.append('post_id', postId);
            formData.append('action', 'like');
            fetch('ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    likeIcon.textContent = data.liked ? '❤️' : '🤍';
                    likesCount.textContent = data.count;
                    likeBtn.classList.toggle('liked', data.liked);
                } else {
                    if (data.message === 'Необходимо авторизоваться.') {
                        alert('Войдите, чтобы поставить лайк.');
                        window.location.href = 'login.php';
                    } else {
                        alert('Ошибка: ' + data.message);
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }
});