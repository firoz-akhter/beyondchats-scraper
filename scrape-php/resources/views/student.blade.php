<div>
  <h1>Student view</h1>
  <table border="1">
    <tr>
      <td>name</td>
      <td>email</td>
      <td>batch</td>
    </tr>
      <br />
    @foreach($data as $student)
    <tr>
      <td> {{$student->name}} </td>
      <td>{{$student->email}} </td>
      <td>{{$student->batch}} </td>
    </tr>
      <br />
  @endforeach
  </table>
</div>