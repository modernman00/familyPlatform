<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Tree</title>
    <style>
        /* CSS styles for the family tree */
        .node {
            width: 100px;
            height: 50px;
            background-color: #eee;
            border: 1px solid #ccc;
            text-align: center;
            line-height: 50px;
            position: absolute;
        }
        .marriage-line {
            position: absolute;
            border-top: 1px solid #ccc;
            width: 1px;
            z-index: -1;
        }
    </style>
</head>
<body>
    <div id="familyTree"></div>

    <script>
        // JavaScript code to generate the family tree
        // Static data for demonstration
        const familyData = {
            name: "John Doe",
            spouse: "Jane Doe",
            children: [
                { name: "Alice Doe", spouse: null, children: ['wale', 'james', 'edu'] },
                { name: "Bob Doe", spouse: "Eve Doe", children: [
                    { name: "Charlie Doe", spouse: null, children: [] }
                ] }
            ]
        };

        // Function to recursively generate the family tree HTML
        function generateFamilyTree(node, left, top) {
            const nodeElem = document.createElement("div");
            nodeElem.className = "node";
            nodeElem.style.left = left + "px";
            nodeElem.style.top = top + "px";
            nodeElem.textContent = node.name;
            document.getElementById("familyTree").appendChild(nodeElem);

            if (node.spouse) {
                const marriageLine = document.createElement("div");
                marriageLine.className = "marriage-line";
                marriageLine.style.left = (left + 50) + "px";
                marriageLine.style.top = (top + 25) + "px";
                marriageLine.style.height = "50px";
                document.getElementById("familyTree").appendChild(marriageLine);

                const spouseElem = document.createElement("div");
                spouseElem.className = "node";
                spouseElem.style.left = (left + 100) + "px";
                spouseElem.style.top = top + "px";
                spouseElem.textContent = node.spouse;
                document.getElementById("familyTree").appendChild(spouseElem);
            }

            node.children.forEach((child, index) => {
                generateFamilyTree(child, left + index * 150, top + 100);
            });
        }

        // Generate the family tree starting from the root node
        generateFamilyTree(familyData, 50, 50);

        
    </script>
</body>
</html>

{{-- <!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg width="800" height="600" xmlns="http://www.w3.org/2000/svg">

  <rect x="100" y="50" width="100" height="50" fill="lightgray" stroke="black" stroke-width="2" />
  <text x="150" y="80" text-anchor="middle">Grandparent 1</text>
  <rect x="600" y="50" width="100" height="50" fill="lightgray" stroke="black" stroke-width="2" />
  <text x="650" y="80" text-anchor="middle">Grandparent 2</text>

  <rect x="250" y="150" width="150" height="75" fill="lightblue" stroke="black" stroke-width="2" />
  <text x="300" y="190" text-anchor="middle">Parent 1</text>
  <rect x="450" y="150" width="150" height="75" fill="lightpink" stroke="black" stroke-width="2" />
  <text x="500" y="190" text-anchor="middle">Parent 2</text>


    <line x1="150" y2="125" x2="150" y2="300" stroke="black" stroke-width="2" />

  <rect x="150" y="300" width="100" height="50" fill="lightgreen" stroke="black" stroke-width="2" />
  <text x="175" y="330" text-anchor="middle">Child 1</text>
  <rect x="350" y="300" width="100" height="50" fill="lightgreen" stroke="black" stroke-width="2" />
  <text x="375" y="330" text-anchor="middle">Child 2</text>
  <rect x="550" y="300" width="100" height="50" fill="lightblue" stroke="black" stroke-width="2" />
  <text x="575" y="330" text-anchor="middle">Child 3</text>

  <line x1="350" y2="125" x2="350" y2="300" stroke="black" stroke-width="2" />

  <rect x="100" y="450" width="75" height="50" fill="lightcoral" stroke="black" stroke-width="2" />
  <text x="125" y="480" text-anchor="middle">Grandchild 1</text>
  <rect x="225" y="450" width="75" height="50" fill="lightcoral" stroke="black" stroke-width="2" />
  <text x="250" y="480" text-anchor="middle">Grandchild 2</text>
  <rect x="475" y="450" width="75" height="50" fill="lightcoral" stroke="black" stroke-width="2" />
  <text x="500" y="480" text-anchor="middle">Grandchild 3</text>
  <rect x="600" y="450" width="75" height="50" fill="lightblue" stroke="black" stroke-width="2" />
  <text x="625" y="480" text-anchor="middle">Grandchild 4</text>



  <line x1="550" y2="125" x2="550 --}}



{{-- <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Family Tree with D3.js</title>
  <script src="https://d3js.org/d3.v7.min.js"></script>
  <style>
    /* CSS styles for the family tree */
    .node {
      fill: #fff;
      stroke: #000;
    }

    .marriage-line {
      fill: none;
      stroke: #ccc;
      stroke-width: 1px;
    }

  </style>
</head>
<body>
  <svg id="familyTree" width="800" height="600"></svg>

  <script>
    // Static family data
    const familyData = {
      name: "John Doe"
      , spouse: "Jane Doe"
      , children: [{
          name: "Alice Doe", 
          spouse: "james", 
          children: [{
            name: "wale",
            spouse: null,
            children: []
          }, {
            name: "james",
            spouse: null,
            children: []
          }]
        }, 
        {
          name: "Bob Doe"
          , spouse: "Eve Doe"
          , children: [{
              name: "Charlie Doe"
              , spouse: null
              , children: []
            }
            , {
              name: "David Doe"
              , spouse: "Grace Doe"
              , children: []
            }
          ]
        }
      ]
    };

    // Create SVG container
    const svg = d3.select("#familyTree");

    // Recursive function to generate family tree
    function generateFamilyTree(node, x, y) {
      const g = svg.append("g").attr("transform", `translate(${x},${y})`);

      // Draw node
      g.append("circle")
        .attr("cx", 0)
        .attr("cy", 0)
        .attr("r", 20)
        .classed("node", true);

      // Add text
      g.append("text")
        .attr("x", 0)
        .attr("y", -25)
        .attr("text-anchor", "middle")
        .text(node.name);

      if (node.spouse) {
        // Draw marriage line
        svg.append("line")
          .attr("x1", x)
          .attr("y1", y)
          .attr("x2", x + 80)
          .attr("y2", y)
          .classed("marriage-line", true);

        // Draw spouse node
        svg.append("circle")
          .attr("cx", x + 100)
          .attr("cy", y)
          .attr("r", 20)
          .classed("node", true);

        // Add spouse text
        svg.append("text")
          .attr("x", x + 100)
          .attr("y", y - 25)
          .attr("text-anchor", "middle")
          .text(node.spouse);
      }

      // Recursively draw children
      if (node.children.length > 0) {
        const numChildren = node.children.length;
        const xOffset = 150;
        const yOffset = 100;

        node.children.forEach((child, i) => {
          generateFamilyTree(child, x - xOffset / 2 + (i * xOffset / (numChildren - 1)), y + yOffset);
        });
      }
    }

    // Generate the family tree starting from the root node
    generateFamilyTree(familyData, 400, 70);

  </script>
</body>
</html> --}}
