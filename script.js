function showSidebar(){
    const sidebar = document.querySelector('.sidebar')
    sidebar.style.display = 'flex'
}
function hideSidebar(){
    const sidebar = document.querySelector('.sidebar')
    sidebar.style.display = 'none'
}


const productContainers = [...document.querySelectorAll('.product-container')];
const nxtBtn = [...document.querySelectorAll('.nxt-btn')];
const preBtn = [...document.querySelectorAll('.pre-btn')];

productContainers.forEach((item, i) => {
    let containerDimensions = item.getBoundingClientRect();
    let containerWidth = containerDimensions.width;

    nxtBtn[i].addEventListener('click', () => {
        item.scrollLeft += containerWidth;
    })

    preBtn[i].addEventListener('click', () => {
        item.scrollLeft -= containerWidth;
    })
})

const newProductContainers = [...document.querySelectorAll('.new-product-container')];
const newNxtBtn = [...document.querySelectorAll('.new-nxt-btn')];
const newPreBtn = [...document.querySelectorAll('.new-pre-btn')];

newProductContainers.forEach((item, i) => {
    let containerDimensions = item.getBoundingClientRect();
    let containerWidth = containerDimensions.width;

    newNxtBtn[i].addEventListener('click', () => {
        item.scrollLeft += containerWidth;
    });

    newPreBtn[i].addEventListener('click', () => {
        item.scrollLeft -= containerWidth;
    });
});

const featuredContainers = [...document.querySelectorAll('.featured-categories-container')];
const featuredNxtBtn = [...document.querySelectorAll('.featured-nxt-btn')];
const featuredPreBtn = [...document.querySelectorAll('.featured-pre-btn')];

featuredContainers.forEach((item, i) => {
    let containerDimensions = item.getBoundingClientRect();
    let containerWidth = containerDimensions.width;

    featuredNxtBtn[i].addEventListener('click', () => {
        item.scrollLeft += containerWidth;
    });

    featuredPreBtn[i].addEventListener('click', () => {
        item.scrollLeft -= containerWidth;
    });
});
