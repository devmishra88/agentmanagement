import * as React from "react";
import Card from "@mui/material/Card";
import CardContent from "@mui/material/CardContent";
import CardMedia from "@mui/material/CardMedia";
import Typography from "@mui/material/Typography";
import { Button, CardHeader, CardActionArea, CardActions } from "@mui/material";

export default function Datacontainer() {
  return (
    <Card sx={{ maxWidth: 345, mb: 1.5 }}>
      <CardActionArea>
        <CardHeader
          title={`Test Item`}
          sx={{
            background: `#1c4e80`,
            color: `#ffffff`,
          }}
        />
        <CardContent
          sx={{
            background: `#F1F1F1`,
          }}
        >
          <Typography gutterBottom variant="h5" component="div">
            Lizard
          </Typography>
          <Typography variant="body2" color="text.secondary">
            Lizards are a widespread group of squamate reptiles, with over 6,000
            species, ranging across all continents except Antarctica
          </Typography>
        </CardContent>
      </CardActionArea>
      <CardActions
        sx={{
          background: `#DADADA`,
        }}
      >
        <Button size="small" color="primary">
          Share
        </Button>
      </CardActions>
    </Card>
  );
}
