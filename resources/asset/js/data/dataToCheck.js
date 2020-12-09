"use strict";
export const dataToCheck = {
	maxLength: {
		id: [
		'firstName','lastName','alias','spouse', 'fatherName', 'motherName', 'motherMaiden','address','postcode','region','country','mobile','email','favSport','footballTeam','passion','occupation'
	 	],
		 max: [15, 15, 15, 15, 30, 30, 15, 50, 10,15,15, 13, 45, 25,30,40,20 ],
	},


	duplicate: {
		email: 'email',
		username: 'username'
	},

	password: {
		pwd : 'password',
		pwd2: 'confirm_password'

	},

	familyCheck: {
		father : ["fatherYes", "fatherNo"],
		mother: ["motherYes", "motherNo"],
		spouse: ["spouseYes", "spouseNo"]	
	}
};