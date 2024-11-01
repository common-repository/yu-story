document.addEventListener("DOMContentLoaded", function() {
    const thumbnails = document.querySelectorAll('#story-thumbnails .story-thumbnail');
    const stories = document.querySelectorAll('#story-container .story');
    const storyContainer = document.getElementById('story-container');
    const storyOverlay = document.getElementById('story-overlay'); // Overlay'i al
    const closeStoryBtn = document.getElementById('close-story'); // Close butonunu al
    let currentStoryIndex = -1;
    let storyDisplayDuration = 3000;
    let storyInterval;

    function showStory(index) {
        if (index >= stories.length) {
            closeStoryOverlay();
            clearInterval(storyInterval);
            return;
        }
        stories.forEach((story, idx) => {
            story.style.display = idx === index ? 'block' : 'none';
        });
        storyOverlay.style.display = 'block';
        storyContainer.style.display = 'block';
    }

    function closeStoryOverlay() {
        storyOverlay.style.display = 'none';
        storyContainer.style.display = 'none';
        if (storyInterval) clearInterval(storyInterval);
    }

    if (closeStoryBtn) {
        closeStoryBtn.addEventListener('click', closeStoryOverlay);
    }
    if (storyOverlay) {
        storyOverlay.addEventListener('click', closeStoryOverlay);
    }

    thumbnails.forEach((thumbnail, index) => {
        thumbnail.addEventListener('click', function() {
            currentStoryIndex = index;
            showStory(index);
            startStorySlideShow();
        });
    });

    function startStorySlideShow() {
        if (storyInterval) clearInterval(storyInterval);
        storyInterval = setInterval(function() {
            currentStoryIndex++;
            if (currentStoryIndex > stories.length - 1) {
                showStory(stories.length);
            } else {
                showStory(currentStoryIndex);
            }
        }, storyDisplayDuration);
    }

    stories.forEach(story => {
        story.addEventListener('click', function(e) {
            e.stopPropagation();
            const storyLink = story.getAttribute('data-story-link');
            if (storyLink) {
                window.open(storyLink, '_blank');
            }
        });
    });

    function resizeOverlay() {
        var overlay = document.getElementById('story-overlay');
        if (overlay) {
            overlay.style.width = window.innerWidth + 'px';
            overlay.style.height = window.innerHeight + 'px';
        }
    }

    window.addEventListener('resize', resizeOverlay);

    resizeOverlay();

    storyOverlay.style.display = 'none';
    storyContainer.style.display = 'none';
});
