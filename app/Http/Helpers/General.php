<?php


function pagination() {


    // session()->put('paginationNumber' ,  request('paginationNumber') ??  PAGINATION_COUNT)  ;

    // return session('paginationNumber');



    // // if( PAGINATION_COUNT != request('paginationNumber') && request('paginationNumber') != null)
    // // {
    // //     session()->put('paginationNumber', request('paginationNumber'));
        
    // //     return session('paginationNumber') ;
    // // }else{
    // //     // session()->put('paginationNumber', PAGINATION_COUNT);
    // //     return PAGINATION_COUNT;
    // // }

    return request('paginationNumber') != 10 && request('paginationNumber') != null  ? request('paginationNumber') : 10;
}

