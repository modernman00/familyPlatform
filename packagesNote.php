/** ryangjchandler/blade-capture-directive

@capture($hello)
Hello, world!
@endcapture

{{ $hello() }}

@capture($hello, $name)
Hello, {{ $name }}!
@endcapture

{{ $hello('Ryan') }}

@capture($hello, $name, $greeting = 'Hello, ')
{{ $greeting }} {{ $name }}!
@endcapture

{{ $hello('Ryan') }}
{{ $hello('Taylor', 'Yo, ') }}

@php($name = 'Ryan')

@capture($hello)
Hello, {{ $name }}!
@endcapture

{{ $hello() }}

We could do in our code $name=strtoupper('Jack Sparrow'). With Pipes, we could do the same as follows:

{{$name | strtoupper}} // JACK SPARROW
We could also add arguments and chain methods.

{{$name | strtoupper | substr:0,5}} // JACK

Include a template with new variables
@include('folder.template',['some' => 'data'])


@compileStamp() // returns the current date and time as "Y-m-d H:i:s"
@compileStamp('d') // returns the current date AND TIME AS "d" (day)

It sets a variable inside the template. Technically, it could allow any PHP expression.




@set($variable="olawale")

{{$variable | strtoupper }}

USING ASSET TO GET THE URL

<img src="@asset('public/avatar/avatarF.png')" alt="checking">

Absolute using @asset

<img src='@asset("img/resource.jpg")' />
is converted to

<img src='http://localhost/img/resource.jpg' />

<head>
    @base
</head>

<body>
    <img src='img/resource.jpg' /> <!-- equivalent to https://www.example.com/urlbase/img/resources.jpg -->
</body>


@forelse($array as $alias) / @empty / @endforelse
Its the same as foreach but jumps to the @empty tag if the array is null or empty

Tag Note Example
$array Is an array with values. $countries
$alias is a new variable that it stores each interaction of the cycle. $country
Example: ($users is an array of objects)

@forelse($users as $user)
<li>{{ $user->name }}</li>
@empty
<p>No users</p>
@endforelse
Returns:

John Doe
Anna Smith

*/