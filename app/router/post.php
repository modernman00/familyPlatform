<?php

// POST UPDATES
$router->map(
  method: 'POST', 
  route: '/member/profilePage/post', 
  target: 'App\controller\members\ProfilePage@post', 
  name: 'profile_page_post');

// GET ALL MEMBERS POST

$router->map(
  method:  'GET', 
  route: '/post/getAllPostCommentByFamCode', 
  target: 'App\controller\members\PostMessage@index', 
  name: 'all_posts');

// GET new post and email

$router->map(
  method: 'GET', 
  route: '/post/getNewPostAndEmail', 
  target: 'App\controller\members\PostMessage@getNewPostAndEmail', name: 'get_new_post_email');

// get new post 
$router->map(
  method: 'GET', 
  route: '/post/getNewPost', 
  target: 'App\controller\members\PostMessage@getNewPostSSE', name: 'get_new_post_FOR_SSE');

  // GET NEW POST
$router->map(
  method: 'GET', 
  route: '/post/getAllPost/update', 
  target: 'App\controller\members\PostMessage@update', 
  name: 'all_posts_update'
);
