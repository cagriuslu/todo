/*
 * Copyright (C) 2013 peredur.net
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

function formhash(form, password)
{
	// Create a new element input, this will be our hashed password field.
	var p = document.createElement("input");

	// Add the new element to our form.
	form.appendChild(p);
	p.name = "p";
	p.type = "hidden";
	p.value = hex_sha512(password.value);

	// Make sure the plaintext password doesn't get sent.
	password.value = "";

	// Finally submit the form.
	form.submit();
}
