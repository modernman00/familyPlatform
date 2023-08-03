public function testSubmitForm(): void
{
    // Case 1: Valid data, successfully insert into the table
    $table = 'users';
    $field = [
        'name' => 'John',
        'email' => 'john@example.com',
        'age' => 30,
    ];
    $result = YourClassName::submitForm($table, $field);
    $this->assertTrue($result);

    // Case 2: Invalid table name, should throw an exception
    $table = 'non_existing_table';
    $field = [
        'name' => 'John',
        'email' => 'john@example.com',
        'age' => 30,
    ];
    $this->expectException(\Exception::class);
    $result = YourClassName::submitForm($table, $field);

    // Case 3: Invalid field value, should throw an exception
    $table = 'users';
    $field = [
        'name' => 'John',
        'email' => 'john@example.com',
        'age' => 'thirty',
    ];
    $this->expectException(\Exception::class);
    $result = YourClassName::submitForm($table, $field);

    // Case 4: Connection error, should return false
    $table = 'users';
    $field = [
        'name' => 'John',
        'email' => 'john@example.com',
        'age' => 30,
    ];
    // Mock the connect2() method to throw an exception
    $this->mock(YourClassName::class)->shouldReceive('connect2')->andThrow(\PDOException::class);
    $result = YourClassName::submitForm($table, $field);
    $this->assertFalse($result);
}